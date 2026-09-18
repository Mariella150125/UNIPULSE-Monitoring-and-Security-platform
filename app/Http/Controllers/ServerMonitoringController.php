<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Services\PrometheusService;
use App\Services\ScoringService;
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
        
        // 1. Sécurité (Wazuh SCA)
        $scoreData = $scoringService->getServerScore($server);

        $security = [
            'sca_score' => $scoreData['score'], // RÈGLE 16 : Renommé en sca_score
            'fim_alerts' => 'Non disponible', // Suppression du mt_rand()
            'open_ports' => [],                // Vide au lieu de faux ports
            'stopped_services' => [],          // Vide au lieu de faux services
            'agent_status' => $server->wazuh_agent_id ? 'active' : 'not_configured'
        ];

        // 2. Uptime
        $uptime = 'Non disponible'; // Suppression de la fausse valeur

        // 3. Historique pour les graphiques
        $timeLabels = [];
        $cpuHistory = [];
        $ramHistory = [];
        $diskHistory = [];
        $networkHistory = [];
        
        // On ne génère plus de fausses données avec mt_rand()
        // Les tableaux restent vides, Chart.js gérera l'affichage "Aucune donnée"

        $logs = []; // Suppression des faux logs

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

        // Si Prometheus n'est pas configuré, on refuse de renvoyer de fausses données
        if (!$prometheus->isConfigured() || !$server->prometheus_instance) {
            return response()->json([
                'success' => false,
                'is_demo' => false,
                'source' => 'prometheus',
                'error' => 'Prometheus non configuré ou instance manquante pour ce serveur.'
            ], 503); // 503 Service Unavailable
        }

        // VRAIES DONNÉES (Quand Prometheus est configuré)
        $instance = $server->prometheus_instance;

        try {
            $status = ((float) $prometheus->getServerStatus($instance) === 1.0) ? 'online' : 'offline';
            $cpu = $prometheus->getCpuUsage($instance);
            $memory = $prometheus->getMemoryUsage($instance);
            $disk = $prometheus->getDiskUsage($instance);

            return response()->json([
                'success' => true,
                'server'  => $server->name,
                'status'  => $status,
                'cpu'     => $cpu !== null ? (float) $cpu : null,
                'memory'  => $memory !== null ? (float) $memory : null,
                'disk'    => $disk !== null ? (float) $disk : null,
                'is_demo' => false
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur de connexion à l\'API Prometheus.'
            ], 500);
        }
    } 
}