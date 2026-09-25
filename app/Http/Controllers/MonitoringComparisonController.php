<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Models\Application;
use App\Models\ApplicationAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class MonitoringComparisonController extends Controller
{
    public function index(Request $request)
    {
        // On récupère les listes pour remplir les menus déroulants
        $servers = Server::orderBy('name')->get();
        $applications = Application::orderBy('name')->get();

        // On récupère les choix de l'utilisateur
        $type = $request->input('type', 'server');
        $entity1 = $request->input('entity1');
        $entity2 = $request->input('entity2');
        $range = $request->input('range', '24h');
        
        // On récupère la métrique choisie
        $metric = $request->input('metric', $type === 'server' ? 'cpu' : 'response_time');
        
        $chartLabels = [];
        $data1 = [];
        $data2 = [];
        $name1 = 'Sélection 1';
        $name2 = 'Sélection 2';
        
        // Le label dynamique de la métrique
        $metricLabel = match($metric) {
            'cpu' => 'Utilisation CPU (%)',
            'ram' => 'Utilisation RAM (%)',
            'disk' => 'Utilisation Disque (%)',
            'network' => 'Trafic Réseau (MB/s)',
            'response_time' => 'Temps de réponse (ms)',
            default => 'Métrique'
        };

        if ($entity1 && $entity2) {
            if ($type === 'server') {
                $s1 = Server::find($entity1);
                $s2 = Server::find($entity2);
                if ($s1) $name1 = $s1->name;
                if ($s2) $name2 = $s2->name;

                // --- VRAIES DONNÉES PROMETHEUS POUR LES SERVEURS ---
                $connector = \App\Models\Connector::where('type', 'prometheus')->first();
                
                if ($connector && $s1 && $s2 && $s1->prometheus_instance && $s2->prometheus_instance) {
                    $base = rtrim($connector->base_url, '/');
                    $port = $connector->api_port ? ':' . $connector->api_port : '';
                    $promUrl = $base . $port . '/api/v1/query_range';

                    $seconds = match($range) { '7d' => 604800, '30d' => 2592000, default => 86400 };
                    $step = match($range) { '7d' => 10800, '30d' => 21600, default => 3600 };

                    $query1 = $this->buildPromQuery($metric, $s1->prometheus_instance);
                    $query2 = $this->buildPromQuery($metric, $s2->prometheus_instance);

                    try {
                        $res1 = Http::timeout(10)->withoutVerifying()->get($promUrl, [
                            'query' => $query1, 'start' => time() - $seconds, 'end' => time(), 'step' => $step
                        ])->json('data')['result'][0]['values'] ?? [];

                        $res2 = Http::timeout(10)->withoutVerifying()->get($promUrl, [
                            'query' => $query2, 'start' => time() - $seconds, 'end' => time(), 'step' => $step
                        ])->json('data')['result'][0]['values'] ?? [];

                        // On fusionne les timestamps
                        foreach ($res1 as $index => $point) {
                            $chartLabels[] = Carbon::createFromTimestamp($point[0])->format('d/m H:i');
                            $data1[] = round((float) $point[1], 2);
                            $data2[] = isset($res2[$index]) ? round((float) $res2[$index][1], 2) : null;
                        }
                    } catch (\Exception $e) {
                        // En cas d'erreur Prometheus, on laisse les tableaux vides
                    }
                }
            } else {
                $a1 = Application::find($entity1);
                $a2 = Application::find($entity2);
                if ($a1) $name1 = $a1->name;
                if ($a2) $name2 = $a2->name;

                // --- VRAIES DONNÉES BDD POUR LES APPLICATIONS ---
                $hours = match($range) { '7d' => 168, '30d' => 720, default => 24 };

                $avail1 = ApplicationAvailability::where('application_id', $entity1)
                    ->where('checked_at', '>=', now()->subHours($hours))
                    ->orderBy('checked_at')->get();
                
                $avail2 = ApplicationAvailability::where('application_id', $entity2)
                    ->where('checked_at', '>=', now()->subHours($hours))
                    ->orderBy('checked_at')->get();

                foreach ($avail1 as $index => $av1) {
                    $chartLabels[] = $av1->checked_at->format('d/m H:i');
                    
                    // CORRECTION ICI : utilisation de response_time au lieu de response_time_ms
                    $data1[] = $av1->response_time ? round($av1->response_time, 2) : null;
                    $data2[] = isset($avail2[$index]) && $avail2[$index]->response_time ? round($avail2[$index]->response_time, 2) : null;
                }
            }
        }

        return view('monitoring.compare', compact(
            'servers', 'applications', 'type', 'entity1', 'entity2', 'range', 'metric',
            'chartLabels', 'data1', 'data2', 'name1', 'name2', 'metricLabel'
        ));
    }

    // Helper pour générer la requête PromQL
    private function buildPromQuery($metric, $instance)
    {
        if (!$instance) return null;
        
        return match($metric) {
            'cpu' => "100 - (avg(rate(node_cpu_seconds_total{mode=\"idle\",instance=\"{$instance}\"}[5m])) * 100)",
            'ram' => "100 * (1 - (node_memory_MemAvailable_bytes{instance=\"{$instance}\"} / node_memory_MemTotal_bytes{instance=\"{$instance}\"}))",
            'disk' => "100 * (1 - (node_filesystem_avail_bytes{mountpoint=\"/\",instance=\"{$instance}\"} / node_filesystem_size_bytes{mountpoint=\"/\",instance=\"{$instance}\"}))",
            'network' => "rate(node_network_receive_bytes_total{instance=\"{$instance}\"}[5m]) / 1024 / 1024",
            default => null,
        };
    }
}