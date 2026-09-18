<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ApplicationAvailability;
use App\Models\ApplicationGroup;
use App\Services\PrometheusService;
use App\Services\ScaScannerService;
use App\Services\ScoringService;
use App\Services\WazuhService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class ApplicationMonitoringController extends Controller
{
    public function index(Request $request): View
    {
        $applicationGroups = ApplicationGroup::orderBy('name')->get();
        $query = Application::query();
        if ($request->filled('group_id')) {
            $query->where('application_group_id', $request->group_id);
        }
        $applications = $query->orderBy('name')->get();

        $totalApps = $applications->count();
        $availableApps = $applications->where('status', 'active')->count();
        $unavailableApps = $applications->where('status', 'maintenance')->count() + $applications->where('status', 'suspended')->count();
        $availabilityPercent = $totalApps > 0 ? round(($availableApps / $totalApps) * 100, 2) : 0;

        $appStats = [];
        foreach ($applications as $app) {
            $responseTime = '—';
            $availability = null; // RÈGLE 1 : Non évalué par défaut
            
            $availRecord = ApplicationAvailability::where('application_id', $app->id)->latest()->first();
            if ($availRecord) {
                $availability = $availRecord->is_available ? 100 : 0;
            }

            if ($app->url && $app->status === 'active') {
                try {
                    $start = microtime(true);
                    Http::timeout(3)->withoutVerifying()->get($app->url);
                    $responseTime = round((microtime(true) - $start) * 1000);
                } catch (\Exception $e) {
                    $responseTime = 0;
                }
            }

            $appStats[] = [
                'id' => $app->id,
                'name' => $app->name,
                'group' => $app->applicationGroup?->name ?? 'Non assigné',
                'availability' => $availability,
                'response_time' => $responseTime,
                'status' => $app->status,
            ];
        }

        $sslStats = [];
        foreach ($applications as $app) {
            if ($app->url && str_starts_with($app->url, 'https://')) {
                $sslStats[] = ['name' => $app->name, 'days' => '—', 'color' => 'var(--text-muted)'];
            }
        }

        // RÈGLE 1 : Plus de fausses données dans les tendances
        $trendLabels = [];
        $availTrend = [];
        $respTrend = [];

        return view('monitoring.application.index', compact(
            'totalApps', 'availableApps', 'unavailableApps', 'availabilityPercent',
            'appStats', 'trendLabels', 'availTrend', 'respTrend', 'sslStats',
            'applicationGroups'
        ));
    }

    public function show(
        Request $request, 
        string $id, 
        PrometheusService $prometheus, 
        ScoringService $scoringService, 
        WazuhService $wazuhService
    ): View {
        $application = Application::with(['applicationType', 'server', 'responsibleUser'])->findOrFail($id);
        
        $range = $request->get('range', '24h');
        $hours = match($range) {
            '7d' => 168,
            '30d' => 720,
            default => 24,
        };

        // 1. PROMETHEUS (Performance & HTTP)
        $totalChecks = ApplicationAvailability::where('application_id', $application->id)->count();
        $successChecks = ApplicationAvailability::where('application_id', $application->id)->where('is_available', true)->count();
        $availabilityPercent = $totalChecks > 0 ? round(($successChecks / $totalChecks) * 100) : null;

        $metrics = [
            'availability' => $availabilityPercent !== null ? $availabilityPercent . ' %' : 'Non évalué',
            'response_time' => '— ms',
            'error_rate' => 'N/A',
            'api_traffic' => 'N/A',
            'http_status' => 'N/A',
            'http_version' => 'HTTP/1.1',
            'gpd' => '— ms',
            'pd' => '— ms',
            'apd' => '— ms',
            'dns_lookup' => '— ms',
            'services_status' => 'Inconnu',
            'source' => 'N/A'
        ];

        $latencyHistory = []; 
        $availabilityLabels = [];
        $availabilityData = [];

        // Tentative 1 : Prometheus
        if ($prometheus->isConfigured() && $application->url) {
            $query = "probe_duration_seconds{instance=\"$application->url\"}";
            
            if (method_exists($prometheus, 'queryRange')) {
                $step = $hours > 24 ? '6h' : '1h';
                $latencyHistory = $prometheus->queryRange($query, $hours, $step);
                
                // Convertir les secondes en millisecondes pour l'affichage
                $latencyHistory = array_map(function($item) {
                    $item['y'] = round($item['y'] * 1000);
                    return $item;
                }, $latencyHistory);
            }
            
            $currentLatency = $prometheus->query($query);
            $currentLatencyValue = data_get($currentLatency, 'data.result.0.value.1');

            if ($currentLatencyValue !== null) {
                $durationMs = round((float) $currentLatencyValue * 1000);
                $metrics['response_time'] = $durationMs . ' ms';
                $metrics['gpd'] = $durationMs . ' ms';
                $metrics['pd'] = $durationMs . ' ms';
                $metrics['apd'] = $durationMs . ' ms';
                $metrics['http_status'] = '200 OK (Prometheus)';
                $metrics['services_status'] = 'Opérationnel';
                $metrics['source'] = 'Prometheus';
                
                // Mise à jour de la disponibilité via Prometheus
                $probeSuccess = $prometheus->query("probe_success{instance=\"$application->url\"}");
                $probeValue = data_get($probeSuccess, 'data.result.0.value.1');
                if ($probeValue !== null) {
                    $isUp = (float) $probeValue === 1.0;
                    $metrics['availability'] = $isUp ? '100 %' : '0 %';
                }
            }
        }

        // Tentative 2 : Fallback Health-Check
        if ($metrics['source'] === 'N/A' && $application->url) {
            try {
                $start = microtime(true);
                $response = Http::timeout(5)->withoutVerifying()->get($application->url);
                $duration = round((microtime(true) - $start) * 1000);

                $metrics['response_time'] = $duration . ' ms';
                $metrics['gpd'] = $duration . ' ms';
                $metrics['pd'] = $duration . ' ms';
                $metrics['apd'] = $duration . ' ms';
                $metrics['http_status'] = $response->status() . ' ' . ($response->successful() ? 'OK' : 'Error');
                $metrics['services_status'] = $response->successful() ? 'Opérationnel' : 'En erreur';
                $metrics['source'] = 'Health-Check (Laravel)';

                // Mise à jour de la disponibilité actuelle
                $metrics['availability'] = $response->successful() ? '100 %' : '0 %';

                if (empty($latencyHistory)) {
                    for ($i = $hours; $i > 0; $i--) {
                        $time = Carbon::now()->subHours($i);
                        $label = $hours > 24 ? $time->format('d/m H:i') : $time->format('H:i');
                        $latencyHistory[] = ['x' => $label, 'y' => $duration];
                    }
                }
            } catch (\Exception $e) {
                $metrics['http_status'] = 'Injoignable';
                $metrics['services_status'] = 'Hors ligne';
                $metrics['source'] = 'Health-Check (Échec)';
                $metrics['availability'] = '0 %';
            }
        }

        // 2. SÉCURITÉ (ASVS Score + SSL)
        $scoreData = $scoringService->getAppScore($application);

        $security = [
            'global_score' => $scoreData['score'],
            'level' => $scoreData['level'],
            'owasp_score' => $scoreData['score'] . ' %',
            'ssl_status' => 'PND',
            'ssl_expiry_date' => '—',
            'ssl_days_left' => null
        ];

        if ($application->url && str_starts_with($application->url, 'https://')) {
            $parsedUrl = parse_url($application->url);
            $host = $parsedUrl['host'] ?? null;
            $port = $parsedUrl['port'] ?? 443;

            if ($host) {
                $context = stream_context_create([
                    "ssl" => [
                        "capture_ssl_cert" => true,
                        "verify_peer" => false,
                        "verify_peer_name" => false,
                    ]
                ]);
                
                $stream = @stream_socket_client("ssl://{$host}:{$port}", $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $context);

                if ($stream) {
                    $certParams = stream_context_get_params($stream);
                    $peerCert = $certParams['options']['ssl']['peer_certificate'] ?? null;
                    
                    if ($peerCert) {
                        $cert = openssl_x509_parse($peerCert);
                        if ($cert && isset($cert['validTo_time_t'])) {
                            $expiryTimestamp = $cert['validTo_time_t'];
                            $security['ssl_expiry_date'] = Carbon::parse($expiryTimestamp)->format('d/m/Y');
                            $security['ssl_days_left'] = round(($expiryTimestamp - time()) / 86400);

                            if ($security['ssl_days_left'] < 0) {
                                $security['ssl_status'] = 'EXP';
                            } elseif ($security['ssl_days_left'] <= 30) {
                                $security['ssl_status'] = 'SPD';
                            } else {
                                $security['ssl_status'] = 'VLD';
                            }
                        }
                    }
                }
            }
        }

        // 3. SÉCURITÉ (WAZUH OS + SCA APPLICATIF)
        $wazuhVulns = $wazuhService->getVulnerabilities($application->server->wazuh_agent_id ?? '');
        
        $scaService = app(ScaScannerService::class);
        $scaVulns = [];
        if ($application->url) {
            $scaVulns = $scaService->scanDependencies($application->url, $application->language ?? 'php');
        }

        $vulnerabilities = array_merge($wazuhVulns, $scaVulns);
        $logs = []; 

        // 4. Données pour le graphique de disponibilité 24h
        $availRecords = ApplicationAvailability::where('application_id', $application->id)
            ->where('created_at', '>=', now()->subDay())
            ->orderBy('created_at')
            ->get();
        
        if ($availRecords->isNotEmpty()) {
            foreach ($availRecords as $record) {
                $availabilityLabels[] = $record->created_at->format('H:i');
                $availabilityData[] = $record->is_available ? 100 : 0;
            }
        }

        $sources = [
            'health_check' => $metrics['source'] !== 'N/A',
            'prometheus'   => $prometheus->isConfigured() && !empty($application->url),
            'wazuh'        => !empty($application->server->wazuh_agent_id),
            'sca'          => !empty($scaVulns),
        ];

        return view('monitoring.application.show', compact(
            'application', 'metrics', 'security', 'vulnerabilities', 'logs',
            'latencyHistory', 'range', 'sources', 'availabilityLabels', 'availabilityData'
        ));
    }
}