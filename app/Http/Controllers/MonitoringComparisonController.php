<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Models\Application;
use Illuminate\Http\Request;

class MonitoringComparisonController extends Controller
{
    public function index(Request $request)
    {
        // On récupère les listes pour remplir les menus déroulants
        $servers = Server::orderBy('name')->get();
        $applications = Application::orderBy('name')->get();

        // On récupère les choix de l'utilisateur
        $type = $request->input('type', 'application');
        $entity1 = $request->input('entity1');
        $entity2 = $request->input('entity2');
        $range = $request->input('range', '24h');
        
        // On récupère la métrique choisie (CPU par défaut pour serveur, Temps de réponse pour app)
        $metric = $request->input('metric', $type === 'server' ? 'cpu' : 'response_time');
        
        // Calcul des heures pour l'axe du temps
        $hours = match($range) { '7d' => 168, '30d' => 720, default => 24 };

        $chartLabels = [];
        $data1 = [];
        $data2 = [];
        $name1 = 'Sélection 1';
        $name2 = 'Sélection 2';
        
        // Le label dynamique de la métrique (sert de titre au graphique)
        $metricLabel = match($metric) {
            'cpu' => 'Utilisation CPU (%)',
            'ram' => 'Utilisation RAM (%)',
            'disk' => 'Utilisation Disque (%)',
            'network' => 'Trafic Réseau (MB/s)',
            'response_time' => 'Temps de réponse (ms)',
            'error_rate' => "Taux d'erreurs (%)",
            'requests' => 'Trafic API (Req/s)',
            default => 'Métrique'
        };

        if ($entity1 && $entity2) {
            if ($type === 'server') {
                $s1 = Server::find($entity1);
                $s2 = Server::find($entity2);
                if ($s1) $name1 = $s1->name;
                if ($s2) $name2 = $s2->name;
            } else {
                $a1 = Application::find($entity1);
                $a2 = Application::find($entity2);
                if ($a1) $name1 = $a1->name;
                if ($a2) $name2 = $a2->name;
            }

            for ($i = $hours; $i > 0; $i--) {
                $time = strtotime("-$i hours");
                $chartLabels[] = $hours > 24 ? date('d/m H:i', $time) : date('H:i', $time);
                
                // On génère des données factices différentes selon la métrique
                $base1 = match($metric) {
                    'cpu' => rand(20, 50),
                    'ram' => rand(40, 70),
                    'disk' => rand(50, 80),
                    'network' => rand(10, 50),
                    'response_time' => rand(100, 200),
                    'error_rate' => rand(0, 5), // Taux d'erreur souvent bas
                    'requests' => rand(50, 150), // Requêtes par seconde
                    default => rand(10, 100)
                };
                
                $base2 = $base1 + rand(-20, 20); 
                
                $data1[] = max(0, $base1 + rand(-10, 10));
                $data2[] = max(0, $base2 + rand(-15, 15));
            }
        }

        return view('monitoring.compare', compact(
            'servers', 'applications', 'type', 'entity1', 'entity2', 'range', 'metric',
            'chartLabels', 'data1', 'data2', 'name1', 'name2', 'metricLabel'
        ));
    }
}