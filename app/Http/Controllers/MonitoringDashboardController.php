<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Models\Application;
use App\Models\WebhookDelivery;
use App\Models\Log;
use Illuminate\Http\Request;

class MonitoringDashboardController extends Controller
{
    public function index()
    {
        $servers = Server::all();
        $apps = Application::all();
        // 1. Calcul Serveurs (en %)
        $totalServers = Server::count();
        $healthyServers = Server::where('global_status', 'healthy')->count();
        $totalServers = $servers->count();
        $healthyServers = $servers->where('global_status', 'healthy')->count();
        
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
        // Le % prend en compte la criticité
        $serverHealthPercent = $totalServersWeight > 0 ? round(($healthyServersWeight / $totalServersWeight) * 100) : 0;

        // --- 2. Applications ---
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
        // Le % prend en compte la criticité
        $appHealthPercent = $totalAppsWeight > 0 ? round(($activeAppsWeight / $totalAppsWeight) * 100) : 0;

        // --- 3. Health Score Global ---
        // Moyenne des deux pourcentages pondérés(criticité , serveurs sains et applications)
        $globalHealthScore = ($totalServersWeight + $totalAppsWeight > 0) ? round(($serverHealthPercent + $appHealthPercent) / 2) : 100;
        // 4. Alertes Critiques (Récupérées des logs ou des webhooks échoués)
        $criticalAlerts = Log::whereIn('level', ['ERROR', 'CRITICAL'])
            ->with('application')
            ->latest('created_at')
            ->limit(5)
            ->get();

        // Si pas de logs, on prend les livraisons de webhooks échouées
        if ($criticalAlerts->isEmpty()) {
            $criticalAlerts = WebhookDelivery::where('success', false)
                ->latest('delivered_at')
                ->limit(5)
                ->get();
        }
            // 5. Données pour la Carte de Dépendances (MF-38)
        $servers = Server::with('applications')->get();
        $nodes = [];
        $edges = [];

        foreach ($servers as $server) {
            // On crée la bulle du Serveur (en forme de carré)
            $nodes[] = ['id' => 'server_' . $server->id, 'label' => $server->name, 'shape' => 'box', 'color' => '#1d4a40', 'font' => ['color' => 'white', 'size' => 14]];
            
            foreach ($server->applications as $app) {
                // On crée la bulle de l'Application (en forme de cercle)
                $nodes[] = ['id' => 'app_' . $app->id, 'label' => $app->name, 'shape' => 'dot', 'color' => '#56825E', 'size' => 15];
                
                // On relie l'application à son serveur
                $edges[] = ['from' => 'app_' . $app->id, 'to' => 'server_' . $server->id, 'color' => '#8a9490'];
            }
        }

        $dependencyNodes = json_encode($nodes);
        $dependencyEdges = json_encode($edges);

        return view('monitoring.dashboard', compact(
            'totalServers', 'healthyServers', 'serverHealthPercent',
            'totalApps', 'activeApps', 'appHealthPercent',
            'globalHealthScore', 'criticalAlerts','dependencyNodes','dependencyEdges'
        ));
    }
}