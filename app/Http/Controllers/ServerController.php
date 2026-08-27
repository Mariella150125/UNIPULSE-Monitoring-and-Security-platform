<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Server;
use App\Models\ServerGroup;
use App\Models\Application;

class ServerController extends Controller
{
    /**
     * Liste avec filtres + KPIs + données graphiques.
     */
    public function index(Request $request)
    {
        $query = Server::with('group')->latest();

        // Recherche
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'ILIKE', "%{$s}%")
                  ->orWhere('hostname', 'ILIKE', "%{$s}%")
                  ->orWhere('ip_address', 'ILIKE', "%{$s}%");
            });
        }

        // Filtre environnement
        if ($request->filled('environment')) {
            $query->where('environment', $request->environment);
        }

        // Filtre statut global
        if ($request->filled('status')) {
            $query->where('global_status', $request->status);
        }

        // Filtre OS
        if ($request->filled('os')) {
            $query->where('os', 'ILIKE', "%{$request->os}%");
        }

        // Filtre groupe
        if ($request->filled('group_id')) {
            $query->where('group_id', $request->group_id);
        }

        $servers = $query->paginate(10);
        $groups = ServerGroup::orderBy('name')->get();

        // KPIs
        $totalServers    = Server::count();
        $activeServers   = Server::where('global_status', 'healthy')->count();
        $criticalServers = Server::where('global_status', 'critical')->count();
        $hostedApps      = Application::whereNotNull('server_id')->count();

        // Graphiques
        $envDistribution = Server::selectRaw('environment, count(*) as total')
            ->groupBy('environment')
            ->orderByDesc('total')
            ->get();

        $osDistribution = Server::selectRaw('os, count(*) as total')
            ->groupBy('os')
            ->orderByDesc('total')
            ->get();

        return view('administration.servers.server', compact(
            'servers',
            'groups',
            'totalServers',
            'activeServers',
            'criticalServers',
            'hostedApps',
            'envDistribution',
            'osDistribution',
        ));
    }

    /**
     * Page détail d'un serveur.
     */
    public function show($id)
    {
        $server = Server::with('group', 'applications')->findOrFail($id);
        return view('administration.servers.show', compact('server'));
    }

    /**
     * Page d'édition d'un serveur.
     */
    public function edit($id)
    {
        $server = Server::findOrFail($id);
        $groups = ServerGroup::orderBy('name')->get();
        return view('administration.servers.edit', compact('server', 'groups'));
    }

    /**
     * Mise à jour un serveur.
     */
    public function update(Request $request, $id)
    {
        $server = Server::findOrFail($id);

        $validated = $request->validate([
            'name'        => 'sometimes|required|string|max:255',
            'hostname'    => 'sometimes|required|string|max:255|unique:servers,hostname,' . $server->id,
            'ip_address'  => 'sometimes|required|ip',
            'port'        => 'nullable|integer|min:1|max:65535',
            'os'          => 'sometimes|required|string|max:100',
            'os_version'   => 'nullable|string|max:100',
            'environment'  => 'sometimes|required|string|max:100',
            'department'   => 'nullable|string|max:100',
            'description'  => 'nullable|string',
            'tags'        => 'nullable|string',
            'group_id'    => 'nullable|exists:server_groups,id',
        ]);

        $server->update($validated);

        return redirect()->route('server.index')->with('success', 'Serveur modifié.');
    }

    /**
     * Page de confirmation de suppression.
     */
    public function delete($id)
    {
        $server = Server::findOrFail($id);
        return view('administration.servers.delete', compact('server'));
    }

    /**
     * Suppression effective d'un serveur.
     */
    public function destroy($id)
    {
        $server = Server::findOrFail($id);
        $server->delete();
        return redirect()->route('servers.index')->with('success', 'Serveur supprimé.');
    }

    /**
     * Créer un serveur.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'hostname'    => 'required|string|max:255|unique:servers,hostname',
            'ip_address'  => 'required|ip',
            'port'        => 'nullable|integer|min:1|max:65535',
            'os'          => 'required|string|max:100',
            'os_version'   => 'nullable|string|max:100',
            'environment'  => 'required|string|max:100',
            'department'   => 'nullable|string|max:100',
            'description'  => 'nullable|string',
            'tags'        => 'nullable|string',
            'group_id'    => 'nullable|exists:server_groups,id',
        ]);

        Server::create($validated);

        return redirect()->route('server.index')->with('success', 'Serveur ajouté avec succès.');
    }

    /**
     * Données graphique — Évolution des alertes.
     * Sera alimenté par les datasources Wazuh quand elles seront prêtes.
     */
    public function alertChartData()
    {
        return response()->json([
            'labels' => [],
            'data'   => [],
        ]);
    }

    /**
     * Données graphique — Répartition par environnement.
     */
    public function envChartData()
    {
        $data = Server::selectRaw('environment, count(*) as total')
            ->groupBy('environment')
            ->orderByDesc('total')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->environment => $item->total
                ];
            });

        return response()->json([
            'labels' => array_keys($data->toArray()),
            'data'   => array_values($data->toArray()),
        ]);
    }
}