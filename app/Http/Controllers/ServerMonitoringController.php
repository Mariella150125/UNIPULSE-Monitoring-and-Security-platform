<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Services\PrometheusService;
use App\Services\ScoringService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ServerMonitoringController extends Controller
{
    // Affiche la liste de tous les serveurs (page principale du module)
    public function index(): View
    {
        $servers = Server::orderBy('name')->get();
        
        // --- KPIs Globaux (Vue d'ensemble) ---
        $totalServers = $servers->count();
        $healthyServers = $servers->where('global_status', 'healthy')->count();
        $criticalServers = $servers->where('global_status', 'critical')->count();
        $maintenanceServers = $servers->where('global_status', 'maintenance')->count();

        return view('monitoring.servers.server', compact(
            'servers', 
            'totalServers', 
            'healthyServers', 
            'criticalServers', 
            'maintenanceServers'
        ));
    }

    // Affiche la fiche détaillée d'un serveur (avec la carte de monitoring)
    public function show(int $id, ScoringService $scoringService): View
    {
        $server = Server::with('applications')->findOrFail($id);
        
        // 1. Sécurité (Wazuh SCA) - On utilise le ScoringService pour récupérer le score CIS
        // (Injektion de dépendance via le paramètre de la méthode, plus propre que app())
        $scoreData = $scoringService->getServerScore($server);

        $security = [
            'cis_score' => $scoreData['score'],
            'fim_alerts' => mt_rand(0, 3),
            'open_ports' => [22 => 'SSH', 80 => 'HTTP', 443 => 'HTTPS'],
            'stopped_services' => ['nginx'],
            'agent_status' => 'active'
        ];

        // 2. Uptime (MF-8)
        $uptime = '14 jours, 6 heures';

        // 3. Historique pour les graphiques (MF-12)
        $timeLabels = [];
        $cpuHistory = [];
        $ramHistory = [];
        $diskHistory = [];
        $networkHistory = [];
        
        for ($i = 24; $i > 0; $i--) {
            $timeLabels[] = Carbon::now()->subHours($i)->format('H:i');
            $cpuHistory[] = mt_rand(20, 65);
            $ramHistory[] = mt_rand(40, 80);
            $diskHistory[] = mt_rand(50, 85); 
            $networkHistory[] = mt_rand(1, 50); // en MB/s
        }

        $logs = [
            [
                'level' => 'ERROR', 
                'source' => 'Système', 
                'message' => 'Espace disque faible', 
                'date' => now()
            ],
        ];

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

        // MODE DÉMONSTRATION : Si Prometheus n'est pas configuré, on simule des données
        if (!$prometheus->isConfigured() || !$server->prometheus_instance) {
            return response()->json([
                'success' => true,
                'server'  => $server->name,
                'status'  => 'online',
                'cpu'     => mt_rand(15, 85) + (mt_rand(0, 99) / 100),
                'memory'  => mt_rand(30, 75) + (mt_rand(0, 99) / 100),
                'disk'    => mt_rand(40, 85) + (mt_rand(0, 99) / 100),
                'is_demo' => true
            ]);
        }

        // VRAIES DONNÉES (Quand Prometheus sera configuré plus tard)
        $instance = $server->prometheus_instance;

        return response()->json([
            'success' => true,
            'server'  => $server->name,
            'status'  => ((float) $prometheus->getServerStatus($instance) === 1.0) ? 'online' : 'offline',
            'cpu'     => $prometheus->getCpuUsage($instance),
            'memory'  => $prometheus->getMemoryUsage($instance),
            'disk'    => $prometheus->getDiskUsage($instance),
            'is_demo' => false
        ]);
    }
}