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

        // Calcul des heures pour l'axe du temps
        $hours = match($range) { '7d' => 168, '30d' => 720, default => 24 };

        $chartLabels = [];
        $data1 = [];
        $data2 = [];
        $name1 = 'Sélection 1';
        $name2 = 'Sélection 2';
        $metricLabel = 'Temps de réponse (ms)';

        // Si l'utilisateur a choisi deux éléments, on génère les données
        if ($entity1 && $entity2) {
            if ($type === 'server') {
                $metricLabel = 'Utilisation CPU (%)';
                $s1 = Server::find($entity1);
                $s2 = Server::find($entity2);
                if ($s1) $name1 = $s1->name;
                if ($s2) $name2 = $s2->name;
            } else {
                $metricLabel = 'Temps de réponse (ms)';
                $a1 = Application::find($entity1);
                $a2 = Application::find($entity2);
                if ($a1) $name1 = $a1->name;
                if ($a2) $name2 = $a2->name;
            }

            // Génération de l'historique (Mock Data pour l'instant)
            for ($i = $hours; $i > 0; $i--) {
                $time = strtotime("-$i hours");
                $chartLabels[] = $hours > 24 ? date('d/m H:i', $time) : date('H:i', $time);
                
                // Deux courbes différentes basées sur une moyenne aléatoire
                $base1 = ($type === 'server') ? rand(20, 50) : rand(100, 200);
                $base2 = ($type === 'server') ? rand(30, 60) : rand(120, 250);
                
                $data1[] = $base1 + rand(-10, 10);
                $data2[] = $base2 + rand(-15, 15);
            }
        }

        return view('monitoring.compare', compact(
            'servers', 'applications', 'type', 'entity1', 'entity2', 'range',
            'chartLabels', 'data1', 'data2', 'name1', 'name2', 'metricLabel'
        ));
    }
}