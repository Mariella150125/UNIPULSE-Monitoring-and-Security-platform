<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlertController extends Controller
{
    public function index(Request $request): View
    {
        // ... (ton code existant pour les KPIs et filtres, ne change pas)
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
            $alertsInPeriod = Alert::whereBetween('created_at', [$start, $end])
                ->selectRaw("EXTRACT(HOUR FROM created_at) as hour, source, COUNT(*) as count")
                ->groupBy('hour', 'source')
                ->get();

            for ($i = 23; $i >= 0; $i--) {
                $h = \Carbon\Carbon::now()->subHours($i);
                $evolutionLabels[] = $h->format('H:i');
                $evolutionSecu[] = $alertsInPeriod->where('hour', (int)$h->format('H'))->whereIn('source', ['wazuh', 'system'])->sum('count');
                $evolutionInfra[] = $alertsInPeriod->where('hour', (int)$h->format('H'))->where('source', 'prometheus')->sum('count');
            }
        } else {
            $alertsInPeriod = Alert::whereBetween('created_at', [$start, $end])
                ->selectRaw("created_at::date as date, source, COUNT(*) as count")
                ->groupBy('date', 'source')
                ->get();

            $interval = \Carbon\CarbonPeriod::create($start, $end);
            foreach ($interval as $date) {
                $evolutionLabels[] = $date->format('d/m');
                $evolutionSecu[] = $alertsInPeriod->where('date', $date->format('Y-m-d'))->whereIn('source', ['wazuh', 'system'])->sum('count');
                $evolutionInfra[] = $alertsInPeriod->where('date', $date->format('Y-m-d'))->where('source', 'prometheus')->sum('count');
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

    public function checkCritical()
    {
        $alerts = Alert::whereNotIn('status', ['resolved', 'closed'])
            ->orderByRaw("CASE priority WHEN 'critical' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 WHEN 'low' THEN 4 ELSE 5 END")
            ->get();

        $hasCritical = $alerts->contains('priority', 'critical');

        return response()->json([
            'has_critical' => $hasCritical,
            'alerts' => $alerts->map(function ($alert) {
                return [
                    'id' => $alert->id,
                    'code' => $alert->code ?? 'ALR-' . $alert->id,
                    'title' => $alert->title,
                    'description' => $alert->description ? substr($alert->description, 0, 40) . '...' : '',
                    'priority' => $alert->priority,
                    'source' => $alert->source,
                ];
            })
        ]);
    }

    // Afficher le détail d'une alerte
    public function show($id)
    {
        $alert = Alert::with(['comments.user'])->findOrFail($id);
        
        return view('alerts.show', compact('alert'));
    }

    // Acquitter une alerte (En cours de traitement)
    public function acknowledge($id)
    {
        $alert = Alert::findOrFail($id);
        $alert->update(['status' => 'acknowledged']);
        
        return redirect()->back()->with('success', 'Alerte acquittée (en cours de traitement).');
    }

    // Résoudre une alerte (Le problème est réglé)
    public function resolve($id)
    {
        $alert = Alert::findOrFail($id);
        $alert->update(['status' => 'resolved']);
        
        return redirect()->back()->with('success', 'Alerte marquée comme résolue.');
    }

    // Assigner une alerte
    public function assign(Request $request, $id)
    {
        $request->validate(['assigned_to' => 'required|exists:users,id']);
        
        $alert = Alert::findOrFail($id);
        $alert->update(['assigned_to' => $request->assigned_to]);
        
        return redirect()->back()->with('success', 'Alerte assignée avec succès.');
    }

    // Fermer une alerte (Archivage définitif)
    public function close($id)
    {
        $alert = Alert::findOrFail($id);
        $alert->update(['status' => 'closed']);
        
        return redirect()->back()->with('success', 'Alerte fermée et archivée.');
    }
        // MF-170 : Ignorer (Snooze) une alerte
    public function snooze(Request $request, $id)
    {
        $request->validate(['minutes' => 'required|integer|min:1|max:1440']);
        $alert = Alert::findOrFail($id);
        
        $alert->update([
            'status' => 'snoozed',
            'snoozed_until' => now()->addMinutes((int) $request->minutes) // <-- CORRIGÉ ICI
        ]);
      
        
        return redirect()->back()->with('success', "Alerte ignorée pendant {$request->minutes} minutes.");
    }

    // MF-165 : Ajouter un commentaire
    public function comment(Request $request, $id)
    {
        $request->validate(['comment' => 'required|string|max:1000']);
        $alert = Alert::findOrFail($id);
        
        // Assurez-vous d'avoir une table 'alert_comments' et le modèle
        \App\Models\AlertComment::create([
            'alert_id' => $alert->id,
            'user_id' => auth()->id(),
            'comment' => $request->comment
        ]);
        
        return redirect()->back()->with('success', 'Commentaire ajouté à l\'historique.');
    }
}