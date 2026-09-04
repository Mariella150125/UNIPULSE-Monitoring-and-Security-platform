<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Services\PrometheusService;
use Illuminate\Http\Request;

class ServerMonitoringController extends Controller
{
    // Affiche la liste de tous les serveurs (page principale du module)
    public function index()
    {
        $servers = Server::orderBy('name')->get();
        return view('monitoring.servers.server', compact('servers'));
    }

    // Affiche la fiche détaillée d'un serveur (avec la carte de monitoring)
    public function show($id)
    {
        $server = Server::with('applications')->findOrFail($id);
        return view('monitoring.servers.show', compact('server'));
    }

    // L'API qui renvoie le JSON pour le JavaScript
    public function metrics($id, PrometheusService $prometheus)
    {
        $server = Server::findOrFail($id);

        // MODE DÉMONSTRATION : Si Prometheus n'est pas configuré, on simule des données
        if (!$prometheus->isConfigured() || !$server->prometheus_instance) {
            return response()->json([
                'success' => true,
                'server'  => $server->name,
                'status'  => 'online',
                'cpu'     => rand(15, 85) + (rand(0, 99) / 100), // Faux CPU
                'memory'  => rand(30, 75) + (rand(0, 99) / 100), // Fausse RAM
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
            'is_demo' => false
        ]);
    }
}