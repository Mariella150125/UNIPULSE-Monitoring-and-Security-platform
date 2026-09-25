<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Server;
use App\Services\ScoringService;
use Illuminate\View\View;
use App\Models\OwaspCategory; 

class SecurityController extends Controller
{
    public function compliance(ScoringService $scoringService): View
    {
        set_time_limit(120);
        $applications = Application::all();
        $servers = Server::all();

        $appScores = [];
        foreach ($applications as $app) {
            $scoreData = $scoringService->getAppScore($app);
            $appScores[] = [
                'id' => $app->id, 'name' => $app->name, 'score' => $scoreData['score'],
                'level' => $scoreData['level'], 'color' => $scoreData['color']
            ];
        }
        
        $serverScores = [];
        foreach ($servers as $server) {
            $scoreData = $scoringService->getServerScore($server);
            $serverScores[] = [
                'id' => $server->id, 'name' => $server->name, 'score' => $scoreData['score'],
                'level' => $scoreData['level'], 'color' => $scoreData['color']
            ];
        }
        // OWASP TOP 10 
        $topVulnerableApps = collect($appScores)->sortBy('score')->take(10)->values()->toArray();
        $topCriticalServers = collect($serverScores)->sortBy('score')->take(10)->values()->toArray();
        $globalAppScore = count($appScores) > 0 ? round(collect($appScores)->avg('score')) : 0;
        $wazuhService = app(\App\Services\WazuhService::class);
        $globalServerScore = $wazuhService->isConfigured() ? round(collect($serverScores)->avg('score')) : null;
        $globalSecurityScore = $globalServerScore !== null ? round(($globalAppScore + $globalServerScore) / 2) : $globalAppScore;
        
        $chartLabels = []; $chartData = []; $chartColors = [];
        foreach (collect($appScores)->sortBy('score')->take(5) as $app) {
            $chartLabels[] = $app['name']; $chartData[] = $app['score']; $chartColors[] = $app['color'];
        }
        foreach (collect($serverScores)->sortBy('score')->take(5) as $server) {
            $chartLabels[] = $server['name']; $chartData[] = $server['score']; $chartColors[] = $server['color'];
        }

        // --- LOGIQUE MIXTE : BDD + CALCUL RÉEL ---
        $owaspCategories = OwaspCategory::where('is_active', true)->get()->toArray();

        // Dictionnaire des checks automatisés par la plateforme
        $automatedChecksMap = [
            'A02' => ['HTTPS obligatoire', 'Certificat SSL valide'],
            'A05' => ['En-têtes HTTP de sécurité'],
            'A06' => ['Dépendances vulnérables', 'Version des dépendances'],
            'A09' => ['Journalisation'],
        ];

        $failedChecks = [];
        foreach ($applications as $app) {
            $scoreData = $scoringService->getAppScore($app);
            foreach ($scoreData['checks'] as $check) {
                if (!$check['is_passed']) $failedChecks[] = $check['name'];
            }
        }

        // On assigne le statut à chaque catégorie de la BDD
        foreach ($owaspCategories as &$cat) {
            $code = $cat['code'];
            if (array_key_exists($code, $automatedChecksMap)) {
                $isFailed = false;
                foreach ($automatedChecksMap[$code] as $checkName) {
                    if (in_array($checkName, $failedChecks)) { $isFailed = true; break; }
                }
                $cat['status'] = $isFailed ? 'Non conforme' : 'Conforme';
            } else {
                $cat['status'] = 'Hors périmètre';
            }
        }
        // -----------------------------------------

        return view('security.compliance', compact(
            'appScores', 'serverScores', 'globalAppScore', 'globalServerScore', 'globalSecurityScore',
            'owaspCategories', 'chartLabels', 'chartData', 'chartColors','topVulnerableApps', // <-- AJOUTÉ ICI
            'topCriticalServers'
        ));
    }
    public function recommendations(ScoringService $scoringService, $id = null): View
    {
        // Si un ID est passé, on filtre. Sinon, on prend tout.
        $query = Application::query();
        if ($id) {
            $query->where('id', $id);
        }
        $applications = $query->get();
        
        $allRecommendations = [];

        foreach ($applications as $app) {
            $scoreData = $scoringService->getAppScore($app);
            
            foreach ($scoreData['checks'] as $check) {
                if (!$check['is_passed']) {
                    $allRecommendations[] = [
                        'app_name' => $app->name,
                        'check_name' => $check['name'],
                        'remediation' => $check['remediation'],
                        'guideline' => $check['guideline'],
                        'severity' => $check['weight'] >= 15 ? 'HIGH' : 'MEDIUM'
                    ];
                }
            }
        }

        usort($allRecommendations, function($a, $b) { 
            return $b['severity'] <=> $a['severity']; 
        });

        return view('security.recommendations', compact('allRecommendations'));
    }
}