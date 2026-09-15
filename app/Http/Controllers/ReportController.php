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
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;

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
        // 1. Gestion de la période (pour les alertes)
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

        // 2. RÉCUPÉRATION DES VRAIES DONNÉES
        $scoringService = app(\App\Services\ScoringService::class);
        $applications = \App\Models\Application::all();
        $servers = \App\Models\Server::all();

        // A. KPIs Ressources
        $totalServers = $servers->count();
        $criticalServers = $servers->where('global_status', 'critical')->count();
        $totalApps = $applications->count();
        $activeApps = $applications->where('status', 'active')->count();

        // B. Sécurité (Vrais scores)
        $appScores = [];
        foreach ($applications as $app) {
            $appScores[] = $scoringService->getAppScore($app)['score'];
        }
        $securityScore = count($appScores) > 0 ? round(array_sum($appScores) / count($appScores)) : 100;
        $owaspCompliance = $securityScore; // On les lie pour l'exemple
        // C. VRAIES VULNÉRABILITÉS WAZUH
        $wazuhService = app(\App\Services\WazuhService::class);
        $openVulns = 0;
        
        foreach ($servers as $server) {
            if (!empty($server->wazuh_agent_id)) {
                $serverVulns = $wazuhService->getVulnerabilities($server->wazuh_agent_id);
                $openVulns += is_array($serverVulns) ? count($serverVulns) : 0;
            }
        }

        // 3. ALERTES (Période)
        $alertsInPeriod = Alert::whereBetween('created_at', [$start, $end])->get();
        $totalAlerts = $alertsInPeriod->count();
        $resolvedAlerts = $alertsInPeriod->where('status', 'resolved')->count();
        $resolutionRate = $totalAlerts > 0 ? round(($resolvedAlerts / $totalAlerts) * 100) : 0;

        // 4. GRAPHIQUES
        // A. Évolution des Alertes
        $evolutionLabels = [];
        $evolutionData = [];
        $interval = \Carbon\CarbonPeriod::create($start, $end);
        $grouped = $alertsInPeriod->groupBy(fn($a) => $a->created_at->format('Y-m-d'));
        foreach ($interval as $date) {
            $evolutionLabels[] = $date->format('d/m');
            $evolutionData[] = $grouped->has($date->format('Y-m-d')) ? $grouped[$date->format('Y-m-d')]->count() : 0;
        }

        // B. Santé des Serveurs (Demi-cercle)
        $serverHealthLabels = ['Sains', 'Critiques', 'Maintenance', 'Inconnus'];
        $serverHealthData = [
            $servers->where('global_status', 'healthy')->count(),
            $criticalServers,
            $servers->where('global_status', 'maintenance')->count(),
            $servers->whereNotIn('global_status', ['healthy', 'critical', 'maintenance'])->count(),
        ];

        // C. Top 5 Apps Vulnérables (Basé sur les vrais scores de ScoringService)
        $appVulnScores = [];
        foreach ($applications as $app) {
            $score = $scoringService->getAppScore($app)['score'];
            // On convertit le score (100 = sécurisé) en nombre de failles (plus le score est bas, plus de failles)
            $appVulnScores[$app->name] = max(0, 100 - $score); 
        }
        arsort($appVulnScores); // Trier du plus vulnérable au moins vulnérable
        $topVulnAppsLabels = array_slice(array_keys($appVulnScores), 0, 5);
        $topVulnsAppsData = array_slice(array_values($appVulnScores), 0, 5);

        // D. Temps de Réponse par Application (Vrai Http::get)
        $topApps = \App\Models\Application::where('status', 'active')->orderBy('name')->take(5)->get();
        $appLabels = [];
        $appResponseData = [];
        $totalResponseTime = 0;

        foreach ($topApps as $app) {
            $appLabels[] = $app->name;
            $responseTime = 0;
            
            // On vérifie que l'URL existe et a l'air valide
            if ($app->url && filter_var($app->url, FILTER_VALIDATE_URL)) {
                try {
                    $startTime = microtime(true);
                    // On met un timeout très court de 2 secondes maximum
                    \Illuminate\Support\Facades\Http::timeout(2)->withoutVerifying()->get($app->url);
                    $responseTime = round((microtime(true) - $startTime) * 1000);
                    $totalResponseTime += $responseTime;
                } catch (\Exception $e) {
                    // Si l'application ne répond pas dans les 2 secondes, on met 0
                    $responseTime = 0; 
                }
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

        $data = [
            'title' => 'Rapport ' . ucfirst($validated['type']),
            'date' => now()->format('d/m/Y H:i'),
            'author' => $user->name,
            'stats' => [
                'Total Alertes' => Alert::count(),
                'Serveurs Critiques' => 2,
                'Score Global Sécurité' => '85%'
            ]
        ];

        if ($validated['format'] === 'pdf') {
            $pdf = Pdf::loadView('reports.pdf', $data);
            Storage::disk('public')->put($filePath, $pdf->output());

        } elseif ($validated['format'] === 'excel') {
            $export = new class($data) implements \Maatwebsite\Excel\Concerns\FromArray {
                protected $data;
                public function __construct($data) { $this->data = $data; }
                public function array(): array {
                    $rows = [
                        [$this->data['title']],
                        ['Généré le', $this->data['date']],
                        ['Auteur', $this->data['author']],
                        [''],
                        ['Statistique', 'Valeur']
                    ];
                    foreach ($this->data['stats'] as $key => $value) {
                        $rows[] = [$key, $value];
                    }
                    return $rows;
                }
            };
            Excel::store($export, $filePath, 'public');

        } elseif ($validated['format'] === 'word') {
            $phpWord = new PhpWord();
            $section = $phpWord->addSection();
            $section->addTitle($data['title'], 1);
            $section->addText('Généré le : ' . $data['date'] . ' par ' . $data['author']);
            $section->addTextBreak(1);
            $section->addTitle('Statistiques Clés', 2);
            foreach ($data['stats'] as $key => $value) {
                $section->addText("- $key : $value");
            }
            
            $tempFile = tempnam(sys_get_temp_dir(), 'word');
            IOFactory::createWriter($phpWord, 'Word2007')->save($tempFile);
            Storage::disk('public')->put($filePath, file_get_contents($tempFile));
            unlink($tempFile);
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

    // Télécharger le fichier généré
    public function download($id)
    {
        $report = Report::findOrFail($id);
        if (Storage::disk('public')->exists($report->file_path)) {
            return Storage::disk('public')->download($report->file_path);
        }
        return redirect()->back()->with('error', 'Fichier introuvable.');
    }
       // Fonction privée pour calculer les vraies données une seule fois
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

    // 1. PDF (DomPDF)
    public function generatePdf()
    {
        $data = $this->getReportData();
        $fileName = 'rapport_stats_' . now()->format('d-m-Y_H-i-s') . '.pdf';
        $filePath = 'reports/' . $fileName;

        $pdf = Pdf::loadView('reports.pdf', $data);
        Storage::disk('public')->put($filePath, $pdf->output());

        $this->saveToHistory($fileName, 'pdf', $filePath);

        return $pdf->download($fileName);
    }

    // 2. EXCEL (Maatwebsite)
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
                    ['Date', $this->data['date']],
                    ['Auteur', $this->data['author']],
                    [''],
                    ['Indicateur', 'Valeur'],
                    ['Total Serveurs', $this->data['totalServers']],
                    ['Serveurs Critiques', $this->data['criticalServers']],
                    ['Applications Actives', $this->data['activeApps'] . ' / ' . $this->data['totalApps']],
                    ['Score de Sécurité', $this->data['securityScore'] . '/100'],
                    ['Total Alertes', $this->data['totalAlerts']],
                    ['Alertes Résolues', $this->data['resolvedAlerts']],
                    ['Taux de Résolution', $this->data['resolutionRate'] . '%'],
                ];
            }
        };

        Excel::store($export, $filePath, 'public');
        $this->saveToHistory($fileName, 'excel', $filePath);

        return Excel::download($export, $fileName);
    }

    // 3. WORD (PhpWord)
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

        $tempFile = tempnam(sys_get_temp_dir(), 'word');
        IOFactory::createWriter($phpWord, 'Word2007')->save($tempFile);
        Storage::disk('public')->put($filePath, file_get_contents($tempFile));
        unlink($tempFile);

        $this->saveToHistory($fileName, 'word', $filePath);

        return response()->download(storage_path('app/public/' . $filePath), $fileName);
    }

    // Fonction pour sauvegarder dans l'historique (MF-177)
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