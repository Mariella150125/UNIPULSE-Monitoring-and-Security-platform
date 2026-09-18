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
        
        // 5. Vrais Scores de Sécurité et Conformité
        $scoringService = app(\App\Services\ScoringService::class);
        $applications = Application::all();
        $servers = Server::all();

        $appScores = [];
        foreach ($applications as $app) {
            try { 
                $appScores[] = $scoringService->getAppScore($app)['score']; 
            } catch (\Exception $e) {}
        }
        $securityScore = count($appScores) > 0 ? round(array_sum($appScores) / count($appScores)) : 0;
        $complianceScore = $securityScore; // Corrélation directe pour le dashboard

        // 6. Données pour les Graphiques (Selon la période)
        $alertLabels = [];
        $alertData = [];
        $securityLabels = [];
        $securityData = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $alertLabels[] = $date->format('d/m');
            $securityLabels[] = $date->format('d/m');
            
            // VRAIES ALERTES
            $alertData[] = Alert::whereDate('created_at', $date->format('Y-m-d'))->count();
            
            // VRAI HISTORIQUE DE SÉCURITÉ (Si la table existe)
            $history = SecurityScoreHistory::where('recorded_at', $date->format('Y-m-d'))->first();
            if ($history) {
                $securityData[] = $history->score;
            } else {
                // Si pas d'historique, on utilise le score actuel pour ne pas avoir de trou
                $securityData[] = $securityScore; 
            }
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