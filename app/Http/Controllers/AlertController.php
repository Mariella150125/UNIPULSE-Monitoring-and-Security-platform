<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    // MF-172 : Afficher la liste des alertes actives
     public function index(Request $request): View
    {
        // 1. Gestion de la période
        $period = $request->get('period', '7');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        if ($period === 'custom' && $startDate && $endDate) {
            $start = \Carbon\Carbon::parse($startDate)->startOfDay();
            $end = \Carbon\Carbon::parse($endDate)->endOfDay();
            $periodText = "du " . $start->format('d/m/Y') . " au " . $end->format('d/m/Y');
        } else {
            $days = in_array($period, ['24', '7', '30', '90']) ? (int)$period : 7;
            if ($days == 24) {
                $start = \Carbon\Carbon::now()->subDay();
                $periodText = "24 dernières heures";
            } else {
                $start = \Carbon\Carbon::now()->subDays($days)->startOfDay();
                $periodText = "$days derniers jours";
            }
            $end = \Carbon\Carbon::now();
            $period = (string)$days;
        }

        // 2. Alertes actives
        $alerts = Alert::whereNotIn('status', ['closed', 'resolved'])
            ->orderByRaw("CASE priority WHEN 'critical' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 WHEN 'low' THEN 4 ELSE 5 END")
            ->latest()->get();

        // 3. KPIs
        $stats = [
            'active' => $alerts->count(),
            'critical' => $alerts->where('priority', 'critical')->count(),
            'resolved' => Alert::where('status', 'resolved')->whereBetween('updated_at', [$start, $end])->count(),
        ];

        // Calcul MTTR
        $resolvedInPeriod = Alert::where('status', 'resolved')->whereBetween('updated_at', [$start, $end])->get();
        $stats['mttr'] = $resolvedInPeriod->count() > 0 ? round($resolvedInPeriod->avg(fn($a) => $a->created_at->diffInMinutes($a->updated_at))) : 0;

        // 4. Données pour le demi-cercle
        $statusData = [
            'Ouvertes' => Alert::where('status', 'open')->count(),
            'Acquittées' => Alert::where('status', 'acknowledged')->count(),
            'Résolues' => Alert::where('status', 'resolved')->count(),
        ];

        // 5. Évolution des alertes (Optimisé pour PostgreSQL)
        $evolutionLabels = [];
        $evolutionSecu = [];
        $evolutionInfra = [];

        if ($period === '24') {
            // Mode 24h : groupé par heure
            $alertsInPeriod = Alert::whereBetween('created_at', [$start, $end])
                ->selectRaw("EXTRACT(HOUR FROM created_at) as hour, source, COUNT(*) as count")
                ->groupBy('hour', 'source')
                ->get();

            for ($i = 23; $i >= 0; $i--) {
                $h = \Carbon\Carbon::now()->subHours($i);
                $evolutionLabels[] = $h->format('H:i');
                
                $secuCount = $alertsInPeriod->where('hour', (int)$h->format('H'))->whereIn('source', ['wazuh', 'system'])->sum('count');
                $infraCount = $alertsInPeriod->where('hour', (int)$h->format('H'))->where('source', 'prometheus')->sum('count');
                
                $evolutionSecu[] = $secuCount;
                $evolutionInfra[] = $infraCount;
            }
        } else {
            // Mode jours : groupé par date
            $alertsInPeriod = Alert::whereBetween('created_at', [$start, $end])
                ->selectRaw("created_at::date as date, source, COUNT(*) as count")
                ->groupBy('date', 'source')
                ->get();

            $interval = \Carbon\CarbonPeriod::create($start, $end);
            foreach ($interval as $date) {
                $evolutionLabels[] = $date->format('d/m');
                $secuCount = $alertsInPeriod->where('date', $date->format('Y-m-d'))->whereIn('source', ['wazuh', 'system'])->sum('count');
                $infraCount = $alertsInPeriod->where('date', $date->format('Y-m-d'))->where('source', 'prometheus')->sum('count');
                $evolutionSecu[] = $secuCount;
                $evolutionInfra[] = $infraCount;
            }
        }

        // 6. Sources majeures
        $majorAlerts = Alert::whereIn('priority', ['high', 'critical'])
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('title, COUNT(*) as count')
            ->groupBy('title')
            ->orderBy('count', 'desc')
            ->take(5)->get();

        $majorSourcesLabels = $majorAlerts->pluck('title')->toArray();
        $majorSourcesData = $majorAlerts->pluck('count')->toArray();

        // 7. Historique résolues
        $resolvedAlerts = Alert::where('status', 'resolved')->latest()->take(10)->get();

        return view('alerts.index', compact(
            'alerts', 'stats', 'statusData', 'periodText', 'period', 'startDate', 'endDate',
            'evolutionLabels', 'evolutionSecu', 'evolutionInfra',
            'majorSourcesLabels', 'majorSourcesData', 'resolvedAlerts'
        ));
    }   
// On modifie checkCritical pour qu'il retourne toutes les alertes actives pour la cloche
    public function checkCritical()
    {
        // On prend toutes les alertes non fermées
        $alerts = Alert::whereNotIn('status', ['resolved', 'closed'])
            ->orderByRaw("CASE priority WHEN 'critical' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 WHEN 'low' THEN 4 ELSE 5 END")
            ->get();

        // On regarde si parmi elles il y en a une critique (pour le son)
        $hasCritical = $alerts->contains('priority', 'critical');

        return response()->json([
            'has_critical' => $hasCritical,
            'alerts' => $alerts->map(function ($alert) {
                return [
                    'id' => $alert->id,
                    'code' => $alert->code ?? 'ALR-'.$alert->id,
                    'title' => $alert->title,
                    'description' => $alert->description ? substr($alert->description, 0, 40) . '...' : '',
                    'priority' => $alert->priority,
                    'source' => $alert->source,
                ];
            })
        ]);
    }

    // NOUVELLE MÉTHODE : Afficher le détail d'une alerte
    public function show($id)
    {
        $alert = Alert::findOrFail($id);
        
        return view('alerts.show', compact('alert'));
    }
    // Acquitter une alerte
    public function acknowledge($id)
    {
        $alert = Alert::findOrFail($id);
        $alert->update(['status' => 'acknowledged']);
        
        return redirect()->back()->with('success', 'Alerte acquittée avec succès.');
    }

    // Assigner une alerte
    public function assign(Request $request, $id)
    {
        $request->validate(['assigned_to' => 'required|exists:users,id']);
        
        $alert = Alert::findOrFail($id);
        $alert->update(['assigned_to' => $request->assigned_to]);
        
        return redirect()->back()->with('success', 'Alerte assignée avec succès.');
    }

    // Fermer une alerte
    public function close($id)
    {
        $alert = Alert::findOrFail($id);
        $alert->update(['status' => 'closed']);
        
        return redirect()->back()->with('success', 'Alerte fermée définitivement.');
    }
   
}