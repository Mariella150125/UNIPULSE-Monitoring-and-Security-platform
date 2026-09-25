<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Application;
use App\Models\ApplicationGroup;
use App\Models\ApplicationType;
use App\Models\AuditLog;
use App\Models\SecurityScoreHistory;
use App\Models\Server;
use App\Models\ServerGroup;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        set_time_limit(120); // Autorise plus de temps car le ScoringService fait des requêtes réseau

        // 1. Gestion de la période (7, 30, 90 jours)
        $period = $request->get('period', '7');
        $days = in_array($period, ['7', '30', '90']) ? (int)$period : 7;
        $start = Carbon::now()->subDays($days)->startOfDay();
        $end = Carbon::now();

        // 2. KPIs Principaux
        $totalApps = Application::count();
        $totalServers = Server::count();
        $totalUsers = User::count();
        
        // 3. KPIs Alertes & Incidents (Sur la période choisie)
        $alertsInPeriod = Alert::whereBetween('created_at', [$start, $end])->get();
        $criticalAlerts = $alertsInPeriod->where('priority', 'critical')->whereNotIn('status', ['resolved', 'closed'])->count();
        $majorAlerts = $alertsInPeriod->where('priority', 'high')->whereNotIn('status', ['resolved', 'closed'])->count();
        $openIncidents = $alertsInPeriod->whereNotIn('status', ['resolved', 'closed'])->count();
        $resolvedIncidents = $alertsInPeriod->where('status', 'resolved')->count();
        
        // 4. Agents Actifs
        $activeAgents = Server::whereNotNull('wazuh_agent_id')->count();
        
        // 5. Vrais Scores de Sécurité et Conformité (Logique complète App + Serveurs)
        $scoringService = app(\App\Services\ScoringService::class);
        $wazuhService = app(\App\Services\WazuhService::class);
        $applications = Application::all();
        $servers = Server::all();

        // A. Score des Applications
        $appScores = [];
        foreach ($applications as $app) {
            try { 
                $appScores[] = $scoringService->getAppScore($app)['score']; 
            } catch (\Exception $e) {}
        }
        $globalAppScore = count($appScores) > 0 ? round(array_sum($appScores) / count($appScores)) : 0;

        // B. Score des Serveurs (Si Wazuh est configuré)
        $wazuhConfigured = $wazuhService->isConfigured();
        $globalServerScore = null;
        
        if ($wazuhConfigured) {
            $serverScores = [];
            foreach ($servers as $server) {
                try {
                    $serverScores[] = $scoringService->getServerScore($server)['score'];
                } catch (\Exception $e) {}
            }
            $globalServerScore = count($serverScores) > 0 ? round(array_sum($serverScores) / count($serverScores)) : 0;
        }

        // C. Score Global final (Moyenne des deux)
        $securityScore = $globalServerScore !== null 
            ? round(($globalAppScore + $globalServerScore) / 2) 
            : $globalAppScore;
            
        $complianceScore = $securityScore; // Corrélation directe pour le dashboard

        // 6. Données pour les Graphiques (Selon la période)
        $alertLabels = [];
        $alertData = [];
        $securityLabels = [];
        $securityData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = \Carbon\Carbon::now()->subDays($i);
            $alertLabels[] = $date->format('d/m');
            $securityLabels[] = $date->format('d/m');
            
            // Compte les vraies alertes critiques créées ce jour-là
            $alertData[] = \App\Models\Alert::whereDate('created_at', $date->format('Y-m-d'))
                                ->where('priority', 'critical')
                                ->count();
            
            // Pour la courbe de sécurité, on affiche le score actuel sur les 7 jours
            $securityData[] = $securityScore; 
        }
        // 7. Derniers Événements & Santé
        $recentEvents = AuditLog::with('user')->latest()->take(4)->get();
        $serversHealth = Server::latest()->take(3)->get();
        $recentAlerts = Alert::where('priority', 'critical')->whereNotIn('status', ['resolved', 'closed'])->latest()->take(2)->get();

        // 8. Données pour les modales (Pour éviter les erreurs de variables undefined)
        $groups = ServerGroup::orderBy('name')->get();
        $applicationTypes = ApplicationType::orderBy('name')->get();
        $servers = Server::orderBy('name')->get();
        $applicationGroups = ApplicationGroup::orderBy('name')->get();

        return view('layout.dashboard', compact(
            'period', 'days',
            'totalApps', 'totalServers', 'totalUsers',
            'criticalAlerts', 'majorAlerts', 'openIncidents', 'resolvedIncidents', 'activeAgents',
            'securityScore', 'complianceScore',
            'alertLabels', 'alertData', 'securityLabels', 'securityData',
            'recentEvents', 'serversHealth', 'recentAlerts',
            'groups', 'applicationTypes', 'servers', 'applicationGroups'
        ));
    }
}