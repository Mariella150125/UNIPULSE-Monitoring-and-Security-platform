<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    // MF-182 : Afficher la liste des rapports générés
    public function index(): View
    {
        $reports = Report::with('generator')->latest()->get();
        return view('reports.index', compact('reports'));
    }

    // Afficher le formulaire de création d'un rapport
    public function create(): View
    {
        return view('reports.creation');
    }

    // MF-178 : Afficher les statistiques à la demande
    public function statistics(Request $request): View
    {
        set_time_limit(120); 
        $period = $request->get('period', '7');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        if ($period === 'custom' && $startDate && $endDate) {
            $start = \Carbon\Carbon::parse($startDate)->startOfDay();
            $end = \Carbon\Carbon::parse($endDate)->endOfDay();
            $periodText = "du " . $start->format('d/m/Y') . " au " . $end->format('d/m/Y');
        } else {
            $days = in_array($period, ['24', '7', '30', '90']) ? (int)$period : 7;
            $start = \Carbon\Carbon::now()->subDays($days)->startOfDay();
            $end = \Carbon\Carbon::now();
            $periodText = "$days derniers jours";
            $period = (string)$days;
        }

        $scoringService = app(\App\Services\ScoringService::class);
        $applications = \App\Models\Application::all();
        $servers = \App\Models\Server::all();

        $totalServers = $servers->count();
        $criticalServers = $servers->where('global_status', 'critical')->count();
        $totalApps = $applications->count();
        $activeApps = $applications->where('status', 'active')->count();

        $appScores = [];
        foreach ($applications as $app) {
            $appScores[] = $scoringService->getAppScore($app)['score'];
        }
        $securityScore = count($appScores) > 0 ? round(array_sum($appScores) / count($appScores)) : 100;
        $owaspCompliance = $securityScore;
        
        $wazuhService = app(\App\Services\WazuhService::class);
        $openVulns = 0;
        foreach ($servers as $server) {
            if (!empty($server->wazuh_agent_id)) {
                $serverVulns = $wazuhService->getVulnerabilities($server->wazuh_agent_id);
                $openVulns += is_array($serverVulns) ? count($serverVulns) : 0;
            }
        }

        $alertsInPeriod = Alert::whereBetween('created_at', [$start, $end])->get();
        $totalAlerts = $alertsInPeriod->count();
        $resolvedAlerts = $alertsInPeriod->where('status', 'resolved')->count();
        $resolutionRate = $totalAlerts > 0 ? round(($resolvedAlerts / $totalAlerts) * 100) : 0;

        $evolutionLabels = [];
        $evolutionData = [];
        $interval = \Carbon\CarbonPeriod::create($start, $end);
        $grouped = $alertsInPeriod->groupBy(fn($a) => $a->created_at->format('Y-m-d'));
        foreach ($interval as $date) {
            $evolutionLabels[] = $date->format('d/m');
            $evolutionData[] = $grouped->has($date->format('Y-m-d')) ? $grouped[$date->format('Y-m-d')]->count() : 0;
        }

        $serverHealthLabels = ['Sains', 'Critiques', 'Maintenance', 'Inconnus'];
        $serverHealthData = [
            $servers->where('global_status', 'healthy')->count(),
            $criticalServers,
            $servers->where('global_status', 'maintenance')->count(),
            $servers->whereNotIn('global_status', ['healthy', 'critical', 'maintenance'])->count(),
        ];

        $appVulnScores = [];
        foreach ($applications as $app) {
            $score = $scoringService->getAppScore($app)['score'];
            $appVulnScores[$app->name] = max(0, 100 - $score); 
        }
        arsort($appVulnScores); 
        $topVulnAppsLabels = array_slice(array_keys($appVulnScores), 0, 5);
        $topVulnsAppsData = array_slice(array_values($appVulnScores), 0, 5);

        $topApps = \App\Models\Application::where('status', 'active')->orderBy('name')->take(5)->get();
        $appLabels = [];
        $appResponseData = [];
        $totalResponseTime = 0;

        foreach ($topApps as $app) {
            $appLabels[] = $app->name;
            $responseTime = 0;
            if ($app->url && filter_var($app->url, FILTER_VALIDATE_URL)) {
                try {
                    $startTime = microtime(true);
                    \Illuminate\Support\Facades\Http::timeout(2)->withoutVerifying()->get($app->url);
                    $responseTime = round((microtime(true) - $startTime) * 1000);
                    $totalResponseTime += $responseTime;
                } catch (\Exception $e) {}
            }
            $appResponseData[] = $responseTime;
        }
        $avgResponseTime = count($topApps) > 0 ? round($totalResponseTime / count($topApps)) : 0;

        return view('reports.statistics', compact(
            'periodText', 'period', 'startDate', 'endDate',
            'totalServers', 'criticalServers', 'totalApps', 'activeApps', 'avgResponseTime',
            'openVulns', 'securityScore', 'owaspCompliance',
            'totalAlerts', 'resolvedAlerts', 'resolutionRate',
            'evolutionLabels', 'evolutionData', 'serverHealthLabels', 'serverHealthData',
            'topVulnAppsLabels', 'topVulnsAppsData'
        ));
    }

    // MF-174, MF-176 : Génération et sauvegarde
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:general,application,server,security,alert,executive',
            'format' => 'required|in:pdf,excel,word',
        ]);

        $user = auth()->user();
        $fileName = 'rapport_' . $validated['type'] . '_' . now()->format('d-m-Y_H-i-s');
        $filePath = 'reports/' . $fileName . '.' . ($validated['format'] === 'excel' ? 'xlsx' : ($validated['format'] === 'word' ? 'docx' : 'pdf'));
        $type = $validated['type'];

        $applications = \App\Models\Application::all();
        $servers = \App\Models\Server::all();
        $alerts = Alert::latest()->limit(15)->get();

        $data = [
            'title' => 'Rapport ' . ucfirst($type),
            'date' => now()->format('d/m/Y'),
            'time' => now()->format('H:i'),
            'author' => $user->name,
            'type' => $type,
            'totalServers' => $servers->count(),
            'criticalServers' => $servers->where('global_status', 'critical')->count(),
            'totalApps' => $applications->count(),
            'activeApps' => $applications->where('status', 'active')->count(),
            'totalAlerts' => Alert::count(),
            'resolvedAlerts' => Alert::where('status', 'resolved')->count(),
        ];

        if (in_array($type, ['general', 'server', 'executive'])) {
            $data['serverList'] = $servers->map(fn($s) => [
                'name' => $s->name, 'ip' => $s->ip_address, 'os' => $s->os, 'status' => $s->global_status
            ])->toArray();
        }

        if (in_array($type, ['general', 'application', 'executive'])) {
            $data['appList'] = $applications->map(fn($a) => [
                'name' => $a->name, 'url' => $a->url ?? 'N/A', 'status' => $a->status
            ])->toArray();
        }

        if (in_array($type, ['general', 'alert', 'executive'])) {
            $data['alertList'] = $alerts->map(fn($a) => [
                'title' => $a->title, 'priority' => $a->priority, 'status' => $a->status, 'date' => $a->created_at->format('d/m/Y H:i')
            ])->toArray();
        }

        if (in_array($type, ['general', 'security', 'executive'])) {
            $scoringService = app(\App\Services\ScoringService::class);
            $appScores = [];
            $appDetails = [];
            foreach ($applications as $app) {
                try {
                    $scoreData = $scoringService->getAppScore($app);
                    $failedChecks = array_filter($scoreData['checks'], fn($c) => !$c['is_passed'] && $c['status'] !== 'Non évalué');
                    $appDetails[] = [
                        'name' => $app->name,
                        'score' => $scoreData['score'],
                        'failures' => array_map(fn($c) => $c['name'], $failedChecks)
                    ];
                    $appScores[] = $scoreData['score'];
                } catch (\Exception $e) {}
            }
            $data['appDetails'] = $appDetails;
            $data['securityScore'] = count($appScores) > 0 ? round(array_sum($appScores) / count($appScores)) : 100;
        }

        // --- RÉCUPÉRATION DES DONNÉES SSL ---
        $sslList = [];
        foreach ($applications as $app) {
            if ($app->url && str_starts_with($app->url, 'https://')) {
                $days = 'N/A';
                $parsedUrl = parse_url($app->url);
                $host = $parsedUrl['host'] ?? null;
                $port = $parsedUrl['port'] ?? 443;
                if ($host) {
                    $context = stream_context_create(["ssl" => ["capture_peer_cert" => true]]);
                    $stream = @stream_socket_client("ssl://{$host}:{$port}", $errno, $errstr, 3, STREAM_CLIENT_CONNECT, $context);
                    if ($stream) {
                        $certParams = stream_context_get_params($stream);
                        $peerCert = $certParams['options']['ssl']['peer_certificate'] ?? null;
                        if ($peerCert) {
                            $cert = openssl_x509_parse($peerCert);
                            if ($cert && isset($cert['validTo_time_t'])) {
                                $days = round(($cert['validTo_time_t'] - time()) / 86400);
                            }
                        }
                    }
                }
                $sslList[] = ['name' => $app->name, 'url' => $app->url, 'days' => $days];
            }
        }
        $data['sslList'] = $sslList;

        // --- GÉNÉRATION DU FICHIER ---
        if ($validated['format'] === 'pdf') {
            $pdf = Pdf::loadView('reports.pdf', $data)->setOption(['isHtml5ParserEnabled' => true, 'defaultFont' => 'DejaVu Sans']);
            Storage::disk('public')->put($filePath, $pdf->output());
            
        } elseif ($validated['format'] === 'excel') {
            $exportData = [];
            $exportData[] = [(string)$data['title']];
            $exportData[] = ['Date', (string)$data['date'], 'Auteur', (string)$data['author']];
            $exportData[] = [''];
            $exportData[] = ['Indicateurs Globaux'];
            $exportData[] = ['Total Serveurs', (string)$data['totalServers']];
            $exportData[] = ['Total Applications', (string)$data['totalApps']];
            $exportData[] = ['Total Alertes', (string)$data['totalAlerts']];
            $exportData[] = ['Score Sécurité', (string)($data['securityScore'] ?? 'N/A') . '%'];
            $exportData[] = [''];
            
            if (isset($data['serverList'])) { 
                $exportData[] = ['I - Server Health Checks']; 
                $exportData[] = ['Nom', 'IP', 'OS', 'Statut']; 
                foreach($data['serverList'] as $s) {
                    $exportData[] = [(string)$s['name'], (string)$s['ip'], (string)$s['os'], (string)$s['status']]; 
                }
                $exportData[] = [''];
            }
            if (isset($data['appList'])) { 
                $exportData[] = ['II - Application Health Checks']; 
                $exportData[] = ['Nom', 'URL', 'Statut']; 
                foreach($data['appList'] as $a) {
                    $exportData[] = [(string)$a['name'], (string)$a['url'], (string)$a['status']]; 
                }
                $exportData[] = [''];
            }
            if (isset($data['sslList'])) { 
                $exportData[] = ['III - SSL Certificates']; 
                $exportData[] = ['Application', 'Jours restants']; 
                foreach($data['sslList'] as $ssl) {
                    $exportData[] = [(string)$ssl['name'], (string)$ssl['days']]; 
                }
            }
            
            $export = new class($exportData) implements \Maatwebsite\Excel\Concerns\FromArray {
                protected $data; 
                public function __construct($data) { $this->data = $data; }
                public function array(): array { return $this->data; }
            };
            Excel::store($export, $filePath, 'public');
            
        } elseif ($validated['format'] === 'word') {
            $phpWord = new PhpWord();
            $section = $phpWord->addSection();
            $section->addTitle((string)$data['title'], 1);
            $section->addText('Date : ' . (string)$data['date'] . ' | Auteur : ' . (string)$data['author']);
            
            if (isset($data['serverList'])) {
                $section->addTitle('I - Server Health Checks', 2);
                $table = $section->addTable();
                $table->addRow(); 
                $table->addCell(3000)->addText('Nom'); 
                $table->addCell(3000)->addText('IP'); 
                $table->addCell(3000)->addText('Statut');
                
                foreach ($data['serverList'] as $s) {
                    $table->addRow();
                    $table->addCell(3000)->addText((string)$s['name']);
                    $table->addCell(3000)->addText((string)$s['ip']);
                    $table->addCell(3000)->addText((string)$s['status']);
                }
            }
            
            if (isset($data['sslList'])) {
                $section->addTitle('III - SSL Certificate Expiration', 2);
                $table = $section->addTable();
                $table->addRow(); 
                $table->addCell(4000)->addText('Application'); 
                $table->addCell(3000)->addText('Jours restants');
                
                foreach ($data['sslList'] as $ssl) {
                    $table->addRow();
                    $table->addCell(4000)->addText((string)$ssl['name']);
                    $table->addCell(3000)->addText((string)$ssl['days']);
                }
            }
            
            $fullPath = Storage::disk('public')->path($filePath);
            if (!file_exists(dirname($fullPath))) {
                mkdir(dirname($fullPath), 0775, true);
            }
            $writer = IOFactory::createWriter($phpWord, 'Word2007');
            $writer->save($fullPath);
        }

        Report::create([
            'name' => $fileName,
            'type' => $validated['type'],
            'format' => $validated['format'],
            'file_path' => $filePath,
            'generated_by' => $user->id,
            'department' => $user->department ?? 'Informatique',
        ]);

        return redirect()->route('reports.index')->with('success', 'Rapport généré avec succès.');
    }

    public function download($id)
    {
        $report = Report::findOrFail($id);
        if (!Storage::disk('public')->exists($report->file_path)) {
            return redirect()->back()->with('error', 'Fichier introuvable.');
        }

        $extension = match($report->format) {
            'excel' => 'xlsx',
            'word'  => 'docx',
            default => 'pdf',
        };

        $downloadName = $report->name . '.' . $extension;
        $physicalPath = Storage::disk('public')->path($report->file_path);

        return response()->download($physicalPath, $downloadName);
    }

    private function getReportData(): array
    {
        $scoringService = app(\App\Services\ScoringService::class);
        $applications = \App\Models\Application::all();
        $servers = \App\Models\Server::all();

        $appScores = [];
        foreach ($applications as $app) {
            $appScores[] = $scoringService->getAppScore($app)['score'];
        }

        $totalAlerts = Alert::count();
        $resolvedAlerts = Alert::where('status', 'resolved')->count();

        return [
            'title' => 'Rapport Analytique Global',
            'date' => now()->format('d/m/Y H:i'),
            'author' => auth()->user()->name ?? 'Système',
            'totalServers' => $servers->count(),
            'criticalServers' => $servers->where('global_status', 'critical')->count(),
            'totalApps' => $applications->count(),
            'activeApps' => $applications->where('status', 'active')->count(),
            'securityScore' => count($appScores) > 0 ? round(array_sum($appScores) / count($appScores)) : 100,
            'totalAlerts' => $totalAlerts,
            'resolvedAlerts' => $resolvedAlerts,
            'resolutionRate' => $totalAlerts > 0 ? round(($resolvedAlerts / $totalAlerts) * 100) : 0,
        ];
    }

    public function generatePdf()
    {
        $scoringService = app(\App\Services\ScoringService::class);
        $applications = \App\Models\Application::all();
        $servers = \App\Models\Server::all();

        $totalServers = $servers->count();
        $criticalServers = $servers->where('global_status', 'critical')->count();
        $totalApps = $applications->count();
        $activeApps = $applications->where('status', 'active')->count();

        $appScores = [];
        foreach ($applications as $app) {
            try { $appScores[] = $scoringService->getAppScore($app)['score']; } catch (\Exception $e) {}
        }
        $securityScore = count($appScores) > 0 ? round(array_sum($appScores) / count($appScores)) : 100;

        $appDetails = [];
        foreach ($applications as $app) {
            $scoreData = $scoringService->getAppScore($app);
            $failedChecks = array_filter($scoreData['checks'], fn($c) => !$c['is_passed'] && $c['status'] !== 'Non évalué');
            $appDetails[] = [
                'name' => $app->name,
                'score' => $scoreData['score'],
                'level' => $scoreData['level'],
                'url' => $app->url,
                'failures' => array_map(fn($c) => $c['name'], $failedChecks)
            ];
        }

        $totalAlerts = Alert::count();
        $resolvedAlerts = Alert::where('status', 'resolved')->count();
        $resolutionRate = $totalAlerts > 0 ? round(($resolvedAlerts / $totalAlerts) * 100) : 0;

        $data = [
            'title' => 'Rapport de Sécurité & Conformité',
            'date' => now()->format('d/m/Y'),
            'time' => now()->format('H:i'),
            'author' => auth()->user()->name ?? 'Système',
            'totalServers' => $totalServers,
            'criticalServers' => $criticalServers,
            'activeApps' => $activeApps,
            'totalApps' => $totalApps,
            'securityScore' => $securityScore,
            'resolutionRate' => $resolutionRate,
            'totalAlerts' => $totalAlerts,
            'resolvedAlerts' => $resolvedAlerts,
            'appDetails' => $appDetails,
        ];

        $pdf = Pdf::loadView('reports.pdf', $data)->setOption(['isHtml5ParserEnabled' => true, 'defaultFont' => 'DejaVu Sans']);
        $pdf->setPaper('a4', 'portrait');
        
        return $pdf->download('Rapport_Securite_UNIPULSE_' . now()->format('d-m-Y') . '.pdf');
    }

    public function generateExcel()
    {
        $data = $this->getReportData();
        $fileName = 'rapport_stats_' . now()->format('d-m-Y_H-i-s') . '.xlsx';
        $filePath = 'reports/' . $fileName;

        $export = new class($data) implements \Maatwebsite\Excel\Concerns\FromArray {
            protected $data;
            public function __construct($data) { $this->data = $data; }
            public function array(): array {
                return [
                    ['Rapport Analytique Global'],
                    ['Date', (string)$this->data['date']],
                    ['Auteur', (string)$this->data['author']],
                    [''],
                    ['Indicateur', 'Valeur'],
                    ['Total Serveurs', (string)$this->data['totalServers']],
                    ['Serveurs Critiques', (string)$this->data['criticalServers']],
                    ['Applications Actives', (string)$this->data['activeApps'] . ' / ' . (string)$this->data['totalApps']],
                    ['Score de Sécurité', (string)$this->data['securityScore'] . '/100'],
                    ['Total Alertes', (string)$this->data['totalAlerts']],
                    ['Alertes Résolues', (string)$this->data['resolvedAlerts']],
                    ['Taux de Résolution', (string)$this->data['resolutionRate'] . '%'],
                ];
            }
        };

        Excel::store($export, $filePath, 'public');
        $this->saveToHistory($fileName, 'excel', $filePath);

        return Excel::download($export, $fileName);
    }

    public function generateWord()
    {
        $data = $this->getReportData();
        $fileName = 'rapport_stats_' . now()->format('d-m-Y_H-i-s') . '.docx';
        $filePath = 'reports/' . $fileName;

        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        $section->addTitle($data['title'], 1);
        $section->addText('Généré le : ' . $data['date'] . ' par ' . $data['author']);
        $section->addTextBreak(1);
        $section->addTitle('Indicateurs Clés', 2);
        $section->addText("- Total Serveurs : " . $data['totalServers']);
        $section->addText("- Serveurs Critiques : " . $data['criticalServers']);
        $section->addText("- Applications Actives : " . $data['activeApps'] . " / " . $data['totalApps']);
        $section->addText("- Score de Sécurité : " . $data['securityScore'] . "/100");
        $section->addTextBreak(1);
        $section->addTitle('Gestion des Alertes', 2);
        $section->addText("- Total des Alertes : " . $data['totalAlerts']);
        $section->addText("- Alertes Résolues : " . $data['resolvedAlerts']);
        $section->addText("- Taux de Résolution : " . $data['resolutionRate'] . "%");

        $fullPath = Storage::disk('public')->path($filePath);
        if (!file_exists(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0775, true);
        }
        
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($fullPath);

        $this->saveToHistory($fileName, 'word', $filePath);

        return response()->download($fullPath, $fileName);
    }

    private function saveToHistory($fileName, $format, $filePath)
    {
        Report::create([
            'name' => $fileName,
            'type' => 'general',
            'format' => $format,
            'file_path' => $filePath,
            'generated_by' => auth()->id() ?? 1,
            'department' => auth()->user()->department ?? 'Informatique',
        ]);
    }    
}