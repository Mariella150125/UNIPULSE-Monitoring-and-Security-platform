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
                'id' => $app->id,
                'name' => $app->name,
                'score' => $scoreData['score'],
                'level' => $scoreData['level'],
                'color' => $scoreData['color']
            ];
        }

        $serverScores = [];
        foreach ($servers as $server) {
            $scoreData = $scoringService->getServerScore($server);
            $serverScores[] = [
                'id' => $server->id,
                'name' => $server->name,
                'score' => $scoreData['score'],
                'level' => $scoreData['level'],
                'color' => $scoreData['color']
            ];
        }

        $topVulnerableApps = collect($appScores)->sortBy('score')->take(10)->values()->toArray();
        $topCriticalServers = collect($serverScores)->sortBy('score')->take(10)->values()->toArray();

        $globalAppScore = count($appScores) > 0 ? round(collect($appScores)->avg('score')) : 100;
        $globalServerScore = count($serverScores) > 0 ? round(collect($serverScores)->avg('score')) : 100;
        $globalSecurityScore = round(($globalAppScore + $globalServerScore) / 2);

        $chartLabels = [];
        $chartData = [];
        $chartColors = [];

        foreach (collect($appScores)->sortBy('score')->take(5) as $app) {
            $chartLabels[] = $app['name'];
            $chartData[] = $app['score'];
            $chartColors[] = $app['color'];
        }
        foreach (collect($serverScores)->sortBy('score')->take(5) as $server) {
            $chartLabels[] = $server['name'];
            $chartData[] = $server['score'];
            $chartColors[] = $server['color'];
        }

        // --- LA MAGIE EST ICI ---
        // On récupère les catégories OWASP actives depuis la base de données.
        // Le jour où la version 2025 sort, tu désactives la 2021 en BDD et tu actives la 2025.
        // Aucune ligne de code à changer !
        $owaspCategories = OwaspCategory::where('is_active', true)->get()->toArray();

        return view('security.compliance', compact(
            'appScores', 'serverScores', 'topVulnerableApps', 'topCriticalServers',
            'globalAppScore', 'globalServerScore', 'globalSecurityScore',
            'owaspCategories', 'chartLabels', 'chartData', 'chartColors'
        ));
    }
    public function recommendations(ScoringService $scoringService): View
    {
        // Injection de dépendance propre
        $applications = Application::all();
        
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