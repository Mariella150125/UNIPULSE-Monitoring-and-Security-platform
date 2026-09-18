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

                // RÈGLE 17 : Gestion des scores partiels
        // Si on a des apps, on calcule le score global basé sur les apps
        $globalAppScore = count($appScores) > 0 ? round(collect($appScores)->avg('score')) : 0;
        
        // Si Wazuh n'est pas configuré, on n'affiche pas un faux score de 100, mais on indique que c'est partiel
        $wazuhService = app(\App\Services\WazuhService::class);
        $wazuhConfigured = $wazuhService->isConfigured(); 
        $globalServerScore = $wazuhConfigured ? round(collect($serverScores)->avg('score')) : null;

        // Le score global n'est calculé que si on a les deux, sinon c'est le score partiel App
        $globalSecurityScore = $globalServerScore !== null ? round(($globalAppScore + $globalServerScore) / 2) : $globalAppScore;
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