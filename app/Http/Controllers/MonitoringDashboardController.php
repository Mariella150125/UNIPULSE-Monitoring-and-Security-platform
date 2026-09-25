<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Models\Application;
use App\Models\Alert;
use App\Services\ScoringService;
use Illuminate\Http\Request;

class MonitoringDashboardController extends Controller
{
    public function index(ScoringService $scoringService)
    {
        $servers = Server::all();
        $apps = Application::all();

        // 1. Calcul Serveurs (en %)
        $totalServers = $servers->count();
        $healthyServers = $servers->where('global_status', 'healthy')->count();
        $criticalServers = $servers->where('global_status', 'critical')->count();
 $maintenanceServers = $servers->where('global_status', 'maintenance')->count();
 $warningServers = $servers->where('global_status', 'warning')->count(); 
        $totalServersWeight = 0;
        $healthyServersWeight = 0;
        foreach ($servers as $server) {
            $weight = match($server->criticality) {
                'critical' => 4, 'high' => 3, 'medium' => 2, default => 1,
            };
            $totalServersWeight += $weight;
            if ($server->global_status === 'healthy') {
                $healthyServersWeight += $weight;
            }
        }
        $serverHealthPercent = $totalServersWeight > 0 ? round(($healthyServersWeight / $totalServersWeight) * 100) : 0;

        // 2. Applications
        $totalApps = $apps->count();
        $activeApps = $apps->where('status', 'active')->count();
        
        $totalAppsWeight = 0;
        $activeAppsWeight = 0;
        foreach ($apps as $app) {
            $weight = match($app->criticality) {
                'critical' => 4, 'high' => 3, 'medium' => 2, default => 1,
            };
            $totalAppsWeight += $weight;
            if ($app->status === 'active') {
                $activeAppsWeight += $weight;
            }
        }
        $appHealthPercent = $totalAppsWeight > 0 ? round(($activeAppsWeight / $totalAppsWeight) * 100) : 0;

        // 3. Health Score Global
        $globalHealthScore = ($totalServersWeight + $totalAppsWeight > 0) ? round(($serverHealthPercent + $appHealthPercent) / 2) : 100;

        // 4. VRAI Niveau de Sécurité (Calculé via ScoringService)
        $appScores = [];
        foreach ($apps as $app) {
            try { 
                $appScores[] = $scoringService->getAppScore($app)['score']; 
            } catch (\Exception $e) {}
        }
        $securityScore = count($appScores) > 0 ? round(array_sum($appScores) / count($appScores)) : 0;
        $securityLevel = $securityScore >= 90 ? 'EXCELLENT' : ($securityScore >= 70 ? 'MOYEN' : ($securityScore >= 50 ? 'FAIBLE' : 'CRITIQUE'));

        // 5. VRAIES Alertes Critiques (Depuis le modèle Alert)
        $criticalAlerts = Alert::where('priority', 'critical')
            ->whereNotIn('status', ['resolved', 'closed'])
            ->latest()
            ->limit(5)
            ->get();

        // 6. Carte de Dépendances
        $serversWithApps = Server::with('applications')->get();
        $nodes = [];
        $edges = [];

        foreach ($serversWithApps as $server) {
            $nodes[] = ['id' => 'server_' . $server->id, 'label' => $server->name, 'shape' => 'box', 'color' => '#1d4a40', 'font' => ['color' => 'white', 'size' => 14]];
            foreach ($server->applications as $app) {
                $nodes[] = ['id' => 'app_' . $app->id, 'label' => $app->name, 'shape' => 'dot', 'color' => '#56825E', 'size' => 15];
                $edges[] = ['from' => 'app_' . $app->id, 'to' => 'server_' . $server->id, 'color' => '#8a9490'];
            }
        }

        $dependencyNodes = json_encode($nodes);
        $dependencyEdges = json_encode($edges);

        return view('monitoring.dashboard', compact(
            'totalServers', 'healthyServers', 'serverHealthPercent',
            'totalApps', 'activeApps', 'appHealthPercent',
            'globalHealthScore', 'securityScore', 'securityLevel', 
            'criticalAlerts', 'dependencyNodes', 'dependencyEdges','warningServers', 'criticalServers', 'maintenanceServers'
        ));
    }
}