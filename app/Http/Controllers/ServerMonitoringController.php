<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Services\PrometheusService;
use App\Services\ScoringService;
use App\Services\WazuhService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class ServerMonitoringController extends Controller
{
    // Affiche la liste de tous les serveurs (page principale du module)
    public function index(): View
    {
        $servers = Server::orderBy('name')->get();
        
        // --- KPIs Globaux (Vue d'ensemble) ---
        $totalServers = $servers->count();
       //"En ligne" = tout ce qui n'est pas "offline" ni en "maintenance"
                // La carte verte ne prend que les serveurs sains ou en avertissement
        $onlineServers = $servers->whereIn('global_status', ['healthy', 'warning'])->count();
        
        // La carte rouge prend les serveurs hors ligne ET critiques
        $offlineServers = $servers->whereIn('global_status', ['offline', 'critical'])->count();
        
        $maintenanceServers = $servers->where('global_status', 'maintenance')->count();

        return view('monitoring.servers.server', compact(
            'servers', 
            'totalServers', 
            'onlineServers', 
            'offlineServers', 
            'maintenanceServers'
        ));
    }

    // Affiche la fiche détaillée d'un serveur
    public function show(int $id, ScoringService $scoringService, PrometheusService $prometheus, WazuhService $wazuh): View
    {
        $server = Server::with('applications')->findOrFail($id);
        $agentId = $server->wazuh_agent_id;
        
        // 1. Sécurité (Wazuh)
        $scoreData = $scoringService->getServerScore($server);

        $security = [
            'sca_score' => $scoreData['score'] ?? 0,
            'fim_alerts' => $agentId ? $wazuh->getFileIntegrityCount($agentId) : 0,
            'open_ports' => $agentId ? $wazuh->getOpenPorts($agentId) : [],
            'stopped_services' => [],
            'agent_status' => $agentId ? $wazuh->getAgentStatus($agentId) : 'not_configured'
        ];

        // 2. Uptime
        $uptime = 'Non disponible';
        if ($server->prometheus_instance) {
            $uptimeSeconds = $prometheus->getUptime($server->prometheus_instance);
            if ($uptimeSeconds) {
                $days = floor($uptimeSeconds / 86400);
                $hours = floor(($uptimeSeconds % 86400) / 3600);
                $uptime = "{$days} jours, {$hours} heures";
            }
        }
        
        // 3. Historique pour les graphiques
        $timeLabels = [];
        $cpuHistory = [];
        $ramHistory = [];
        $diskHistory = [];
        $networkHistory = [];

        // 4. Logs
        $logs = $agentId ? $wazuh->getSecurityEvents($agentId, 15) : [];

        return view('monitoring.servers.show', compact(
            'server', 
            'security', 
            'uptime', 
            'timeLabels', 
            'cpuHistory', 
            'ramHistory', 
            'diskHistory', 
            'networkHistory', 
            'logs'
        ));
    }

    // L'API qui renvoie le JSON pour le JavaScript
    public function metrics(int $id, PrometheusService $prometheus): JsonResponse
    {
        $server = Server::findOrFail($id);

        if (!$prometheus->isConfigured() || !$server->prometheus_instance) {
            return response()->json([
                'success' => false,
                'is_demo' => false,
                'source' => 'prometheus',
                'error' => 'Prometheus non configuré ou instance manquante pour ce serveur.'
            ], 503);
        }

        $instance = $server->prometheus_instance;

        try {
            $status = ((float) $prometheus->getServerStatus($instance) === 1.0) ? 'online' : 'offline';
            $cpu = $prometheus->getCpuUsage($instance);
            $memory = $prometheus->getMemoryUsage($instance);
            $disk = $prometheus->getDiskUsage($instance);
            $network = $prometheus->getNetworkTraffic($instance);

            return response()->json([
                'success' => true,
                'server'  => $server->name,
                'status'  => $status,
                'cpu'     => $cpu !== null ? (float) $cpu : null,
                'memory'  => $memory !== null ? (float) $memory : null,
                'disk'    => $disk !== null ? (float) $disk : null,
                'network' => $network !== null ? (float) $network : null,
                'is_demo' => false
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur de connexion à l\'API Prometheus.'
            ], 500);
        }
    } 

    // Récupère les VRAIES données historiques de Prometheus pour les graphiques
    public function history(int $id, Request $request)
    {
        $server = Server::findOrFail($id);
        $instance = $server->prometheus_instance;
        
        if (!$instance) {
            return response()->json(['success' => false, 'error' => 'Instance non configurée'], 400);
        }

        $connector = \App\Models\Connector::where('type', 'prometheus')->first();
        if (!$connector) {
            return response()->json(['success' => false, 'error' => 'Connecteur Prometheus manquant'], 400);
        }

        $base = rtrim($connector->base_url, '/');
        $port = $connector->api_port ? ':' . $connector->api_port : '';
        $promUrl = $base . $port . '/api/v1/query_range';
        
        $metric = $request->get('metric');
        $range = $request->get('range', '24h');
        
        $seconds = match($range) {
            '30min' => 1800, '48h' => 172800, '7d' => 604800, '30d' => 2592000, default => 86400,
        };
        $step = $range == '30min' ? 60 : 3600;
        
        $query = match($metric) {
            'cpu' => "100 - (avg(rate(node_cpu_seconds_total{mode=\"idle\",instance=\"{$instance}\"}[5m])) * 100)",
            'ram' => "100 * (1 - (node_memory_MemAvailable_bytes{instance=\"{$instance}\"} / node_memory_MemTotal_bytes{instance=\"{$instance}\"}))",
            'disk' => "100 * (1 - (node_filesystem_avail_bytes{mountpoint=\"/\",instance=\"{$instance}\"} / node_filesystem_size_bytes{mountpoint=\"/\",instance=\"{$instance}\"}))",
            'network' => "rate(node_network_receive_bytes_total{instance=\"{$instance}\"}[5m]) / 1024 / 1024",
            default => null,
        };

        if (!$query) return response()->json(['success' => false], 400);

        try {
            $response = Http::timeout(10)->withoutVerifying()->get($promUrl, [
                'query' => $query,
                'start' => time() - $seconds,
                'end' => time(),
                'step' => $step
            ]);

            $data = $response->json('data')['result'][0]['values'] ?? [];
            
            $labels = [];
            $values = [];
            // On définit le format selon la période
            $format = in_array($range, ['7d', '30d']) ? 'd/m' : 'H:i';

            foreach ($data as $point) {
                $labels[] = \Carbon\Carbon::createFromTimestamp($point[0])->format($format);
                $values[] = round((float) $point[1], 2);
            }

            return response()->json([
                'success' => true,
                'labels' => $labels,
                'values' => $values
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Erreur Prometheus: ' . $e->getMessage()], 200);
        }
    }

    // Récupère l'historique CPU de TOUS les serveurs pour le graphique global
    public function globalCpuHistory(Request $request)
    {
        $connector = \App\Models\Connector::where('type', 'prometheus')->first();
        if (!$connector) {
            return response()->json(['success' => false, 'error' => 'Connecteur Prometheus manquant'], 400);
        }

        $base = rtrim($connector->base_url, '/');
        $port = $connector->api_port ? ':' . $connector->api_port : '';
        $promUrl = $base . $port . '/api/v1/query_range';
        
        $range = $request->get('range', '1h');
        
        $seconds = match($range) {
            '24h' => 86400, '7d'  => 604800, '30d' => 2592000, '90d' => 7776000, default => 3600,
        };
        
        $step = match($range) {
            '1h'  => 60, '24h' => 3600, '7d'  => 10800, '30d' => 21600, '90d' => 86400, default => 3600,
        };

        $query = '100 - (avg by (instance) (rate(node_cpu_seconds_total{mode="idle"}[5m])) * 100)';

        try {
            $response = Http::timeout(15)->withoutVerifying()->get($promUrl, [
                'query' => $query,
                'start' => time() - $seconds,
                'end' => time(),
                'step' => $step
            ]);

            $results = $response->json('data')['result'] ?? [];
            $servers = Server::whereNotNull('prometheus_instance')->pluck('name', 'prometheus_instance');
            
            $datasets = [];
            $masterLabels = [];
            $palette = ['#1d4a40', '#e08e3e', '#c0392b', '#56825E', '#2980b9', '#8e44ad'];
            $colorIndex = 0;

            foreach ($results as $series) {
                $instance = $series['metric']['instance'] ?? 'Inconnu';
                $serverName = $servers[$instance] ?? $instance;
                
                $values = [];
                $labels = [];

                                // On définit le format de date selon la période choisie
                $format = in_array($range, ['7d', '30d', '90d']) ? 'd/m' : 'H:i';

                foreach ($series['values'] as $point) {
                    $labels[] = \Carbon\Carbon::createFromTimestamp($point[0])->format($format);
                    $values[] = round((float) $point[1], 2);
                }
                
                if (empty($masterLabels)) {
                    $masterLabels = $labels;
                }

                $color = $palette[$colorIndex % count($palette)];
                $colorIndex++;

                $datasets[] = [
                    'label' => $serverName,
                    'data' => $values,
                    'borderColor' => $color,
                    'backgroundColor' => 'transparent',
                    'fill' => false,
                    'tension' => 0.4,
                    'pointRadius' => 2
                ];
            }

            return response()->json([
                'success' => true,
                'labels' => $masterLabels,
                'datasets' => $datasets
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Erreur Prometheus: ' . $e->getMessage()], 200);
        }
    }
        // Récupère l'historique des alertes des SERVEURS
    public function alertHistory(Request $request)
    {
        $range = $request->get('range', '24h');
        $hours = match($range) {
            '7d' => 168, '30d' => 720, default => 24,
        };

        $isLongRange = $hours > 24;
        $trunc = $isLongRange ? 'day' : 'hour';
        $format = $isLongRange ? 'd/m' : 'H:i';

        // On filtre les alertes qui viennent des serveurs (Prometheus ou Wazuh)
        $alertsData = \App\Models\Alert::whereIn('source', ['prometheus', 'wazuh'])
            ->where('created_at', '>=', now()->subHours($hours))
            ->selectRaw("DATE_TRUNC('{$trunc}', created_at) as period, COUNT(*) as count")
            ->groupByRaw("DATE_TRUNC('{$trunc}', created_at)")
            ->orderBy('period')
            ->get();

        $labels = [];
        $data = [];

        // On remplit les periods vides avec 0
        $start = now()->subHours($hours);
        $end = now();
        $interval = $isLongRange ? \Carbon\CarbonInterval::day() : \Carbon\CarbonInterval::hour();
        $period = \Carbon\CarbonPeriod::create($start, $interval, $end);
        
        $dataMap = [];
        foreach ($alertsData as $item) {
            $dataMap[\Carbon\Carbon::parse($item->period)->format($format)] = $item->count;
        }

        foreach ($period as $date) {
            $label = $date->format($format);
            $labels[] = $label;
            $data[] = $dataMap[$label] ?? 0;
        }

        return response()->json([
            'success' => true,
            'labels' => $labels,
            'data' => $data
        ]);
    }
}