<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreConnectorRequest;
use App\Http\Requests\UpdateConnectorRequest;
use App\Models\Connector;
use App\Services\ConnectorService;
use App\Services\Connectors\ConnectorTesterFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class ConnectorController extends Controller
{
    public function __construct(
        private readonly ConnectorService $service,
    ) {}

    /**
     * Liste des connecteurs avec filtres et KPIs.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Connector::where('created_by', $user->id);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'ILIKE', "%{$s}%")
                  ->orWhere('base_url', 'ILIKE', "%{$s}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $connectors = $query->latest()->paginate(10);

        $all = Connector::where('created_by', $user->id)->get();
        $kpis = [
            'total'     => $all->count(),
            'connected' => $all->where('status', 'connected')->count(),
            'error'     => $all->where('status', 'error')->count(),
            'never'     => $all->where('status', 'never_tested')->count(),
        ];
        $lastSync = \App\Models\Connector::whereNotNull('last_check_at')->max('last_check_at');
        return view('administration.connectors.agent', compact('connectors', 'kpis','lastSync'));
    }

    /**
     * Créer un connecteur.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:prometheus,wazuh',
            'name' => 'required|string|max:255',
            'base_url' => 'required|string|max:255',
            'api_port' => 'nullable|integer|min:1|max:65535',
            'auth_username' => 'nullable|string|max:255',
            'auth_password' => 'nullable|string|max:255',
            'extra_config' => 'nullable|string',
        ]);

        $connector = new Connector();
        $connector->type = $validated['type'];
        $connector->name = $validated['name'];
        $connector->base_url = $validated['base_url'];
        $connector->api_port = $validated['api_port'] ?? null;
        $connector->auth_username = $validated['auth_username'] ?? null;
        $connector->created_by = Auth::id();
        $connector->updated_by = Auth::id(); // <-- AJOUTE CETTE LIGNE
        
        // Chiffrement explicite du mot de passe
        if (!empty($validated['auth_password'])) {
            $connector->encryptPassword($validated['auth_password']);
        }
        
        $connector->save();

        return redirect()->route('connectors.index')->with('success', 'Connecteur créé avec succès.');
    }

    /**
     * Page détail d'un connecteur.
     */
    public function show($id)
    {
        $connector = Connector::findOrFail($id);
        $logs = $connector->recentLogs(20);

        return view('administration.connectors.show', compact('connector', 'logs'));
    }
        // Changer le statut (Activer / Désactiver)
    public function changeStatus(Request $request, $id)
    {
        $connector = Connector::findOrFail($id);
        $connector->status = $request->input('status', 'never_tested');
        $connector->save();

        return redirect()->back()->with('success', 'Statut du connecteur mis à jour avec succès.');
    }

    /**
     * Page d'édition d'un connecteur.
     */
    public function edit($id)
    {
        $connector = Connector::findOrFail($id);

        return view('administration.connectors.edit', compact('connector'));
    }

    /**
     * Mettre à jour un connecteur.
     */
    public function update(UpdateConnectorRequest $request, $id)
    {
        $connector = Connector::findOrFail($id);

        if ($connector->created_by !== Auth::id()) abort(403);

        $this->service->update(connector: $connector, data: $request->validated(), userId: Auth::id());

        return redirect()->route('connectors.index')->with('success', 'Connecteur modifié.');
    }

    /**
     * Page de confirmation de suppression.
     */
    public function delete($id)
    {
        $connector = Connector::findOrFail($id);

        return view('administration.connectors.delete', compact('connector'));
    }

    /**
     * Supprimer un connecteur.
     */
    public function destroy($id)
    {
        $connector = Connector::findOrFail($id);

        //if ($connector->created_by !== Auth::id()) abort(403);

        //$this->service->delete($connector);
        $connector->delete();
        return redirect()->route('connectors.index')->with('success', 'Connecteur supprimé.');
    }

    /**
     * Page de test de connexion.
     */
    public function plug($id)
    {
        $connector = Connector::findOrFail($id);
        $logs = $connector->recentLogs(5);

        return view('administration.connectors.plug', compact('connector', 'logs'));
    }

    /**
     * Test de connexion (AJAX, appelé depuis la page plug ou la liste).
     */
    public function test(Connector $connector): JsonResponse
    {
        if ($connector->created_by !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Non autorisé.'], 403);
        }

        // Appelle le service qui fait le test ET sauvegarde le log en BDD
        $result = $this->service->testConnection($connector);

        return response()->json([
            'success'       => $result->success,
            'message'       => $result->message,
            'response_time' => $result->response_time,
            'status'        => $result->status,
            'metadata'      => $result->metadata,
            'last_check_at' => $connector->fresh()->last_check_at?->diffForHumans(),
            'last_log'      => $result->last_log, // <-- On renvoie le log au JS
        ]);
    }

    /**
     * Données d'un connecteur en JSON (pour la modale d'édition rapide).
     */
    public function editData(Connector $connector): JsonResponse
    {
        if ($connector->created_by !== Auth::id()) abort(403);

        return response()->json([
            'type'          => $connector->type,
            'name'          => $connector->name,
            'base_url'      => $connector->base_url,
            'api_port'      => $connector->api_port,
            'auth_username' => $connector->auth_username,
            'has_password'  => $connector->has_password,
            'extra_config'  => $connector->extra_config,
        ]);
    }

    /**
     * Test de connexion depuis la modale, avant d'enregistrer (AJAX).
     */
    public function testPreview(Request $request): JsonResponse
    {
        $request->validate([
            'type'          => 'required|in:prometheus,wazuh',
            'base_url'      => 'required|url',
            'auth_username' => 'nullable|string',
            'auth_password' => 'nullable|string',
            'api_port'      => 'nullable|integer|min:1|max:65535',
        ]);

        $tempConnector = new Connector([
            'type'                    => $request->type,
            'name'                    => 'Test temporaire',
            'base_url'                => $request->base_url,
            'auth_username'           => $request->auth_username,
            'auth_password_encrypted' => $request->auth_password ? Crypt::encryptString($request->auth_password) : null,
            'api_port'                => $request->api_port,
            'extra_config'            => $request->extra_config,
            'status'                  => 'never_tested',
        ]);

        $start = microtime(true);
        $tester = ConnectorTesterFactory::make($tempConnector);
        $result = $tester->test();
        $duration = round((microtime(true) - $start) * 1000, 2);

        return response()->json([
            'success'       => $result->success,
            'message'       => $result->message,
            'response_time' => $duration,
            'metadata'      => $result->metadata,
        ]);
    }
}