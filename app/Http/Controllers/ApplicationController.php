<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\ApplicationType;
use App\Models\Server;
use App\Models\User;
use App\Models\ApplicationAvailability;

class ApplicationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // ==========================================
    // 1. REQUÊTE PRINCIPALE
    // ==========================================

        $query = Application::with([
            'applicationType',
            'server',
            'responsibleUser'
        ]);


        // ==========================================
        // 2. RECHERCHE
        // ==========================================

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('name', 'like', '%' . $search . '%')
                ->orWhere('identifiant_genere', 'like', '%' . $search . '%')
                ->orWhere('description', 'like', '%' . $search . '%')
                ->orWhere('url', 'like', '%' . $search . '%');

            });
        }


        // ==========================================
        // 3. FILTRE ENVIRONNEMENT
        // ==========================================

        if ($request->filled('environment')) {

            $query->where(
                'environment',
                $request->environment
            );
        }


        // ==========================================
        // 4. FILTRE STATUT
        // ==========================================

        if ($request->filled('status')) {

            $query->where(
                'status',
                $request->status
            );
        }


        // ==========================================
        // 5. APPLICATIONS PAGINÉES
        // ==========================================

        $applications = $query
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();


        // ==========================================
        // 6. TYPES D'APPLICATION
        // ==========================================

       
            $applicationTypes = ApplicationType::where('status', true)
                ->orderBy('name')
                ->get();

            $servers = Server::orderBy('name')->get();

            $users = User::orderBy('name')->get();

            $activeApplications = Application::where('status', 'active')->count();


        // ==========================================
        // STATISTIQUES PAR ENVIRONNEMENT
        // ==========================================

        $environmentStats = Application::selectRaw(
            'environment, COUNT(*) as total'
        )
        ->groupBy('environment')
        ->get();


        // ==========================================
// DISPONIBILITÉ DES 7 DERNIERS JOURS
// ==========================================

    $availabilityStats = ApplicationAvailability::query()
        ->where('checked_at', '>=', now()->subDays(7))
        ->selectRaw('DATE(checked_at) as date')
        ->selectRaw(
            'AVG(CASE WHEN is_available = true THEN 100 ELSE 0 END) as availability'
        )
        ->groupByRaw('DATE(checked_at)')
        ->orderBy('date')
        ->get();
            return view(
                'administration.applis.appli',
                compact(
                    'applications',
                    'applicationTypes',
                    'servers',
                    'users',
                    'activeApplications',
                    'environmentStats',
                    'availabilityStats'
                )
            );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $applicationTypes = ApplicationType::where('status', true)
            ->orderBy('name')
            ->get();

        $servers = Server::orderBy('name')->get();

        $users = User::orderBy('name')->get();

        return view(
            'administration.applis.create',
            compact(
                'applicationTypes',
                'servers',
                'users'
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // ==========================================
        // 1. VALIDATION
        // ==========================================

        $validated = $request->validate([

            // Identification
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',

            // Informations techniques
            'url' => 'nullable|url|max:255',
            'language' => 'nullable|string|max:100',
            'framework' => 'nullable|string|max:100',
            'version' => 'nullable|string|max:50',

            // Classification
            'application_type_id' => [
                'required',
                'exists:application_types,id'
            ],

            'environment' => [
                'required',
                'in:development,test,staging,production'
            ],

            'database_used' => 'nullable|string|max:100',

            // Informations métier
            'client_name' => 'nullable|string|max:255',
            'tags' => 'nullable',

            // Status
            'status' => [
                'nullable',
                'in:development,testing,staging,active,maintenance,suspended,retired'
            ],

            // Hébergement
            'is_hosted' => 'required|boolean',

            'server_id' => [
                'nullable',
                'exists:servers,id'
            ],

            'port' => 'nullable|integer|min:1|max:65535',

            'deployment_path' => 'nullable|string|max:500',

            // Responsable
            'responsible_user_id' => [
                'nullable',
                'exists:users,id'
            ],

            // Monitoring Prometheus
            'monitoring_enabled' => 'nullable|boolean',

            'prometheus_job' => 'nullable|string|max:255',

            'metrics_endpoint' => 'nullable|string|max:255',

            'scrape_interval' => 'nullable|string|max:50',

            'url_health_check' => 'nullable|url|max:500',

            // Wazuh
            'wazuh_enabled' => 'nullable|boolean',

            // Criticité
            'criticality' => [
                'nullable',
                'in:low,medium,high,critical'
            ],
        ]);

        // ==========================================
        // 2. HÉBERGEMENT
        // ==========================================

        if ($validated['is_hosted']) {

            if (empty($validated['server_id'])) {

                return back()
                    ->withErrors([
                        'server_id' =>
                            'Un serveur est obligatoire pour une application hébergée.'
                    ])
                    ->withInput();
            }

        } else {

            // Une application non hébergée
            // ne doit pas avoir de serveur.

            $validated['server_id'] = null;
            $validated['port'] = null;
            $validated['deployment_path'] = null;
        }

        // ==========================================
        // 3. VALEURS PAR DÉFAUT
        // ==========================================

        $validated['status'] =
            $validated['status'] ?? 'planned';

        $validated['monitoring_enabled'] =
            $validated['monitoring_enabled'] ?? false;

        $validated['wazuh_enabled'] =
            $validated['wazuh_enabled'] ?? false;

        // ==========================================
        // 4. GÉNÉRATION DE L'IDENTIFIANT
        // ==========================================

        $prefix = $validated['is_hosted']
            ? 'APN-H-'
            : 'APN-NH-';

        $lastApplication = Application::where(
            'identifiant_genere',
            'like',
            $prefix . '%'
        )
        ->orderByDesc('id')
        ->first();

        if ($lastApplication) {

            $lastNumber = (int) str_replace(
                $prefix,
                '',
                $lastApplication->identifiant_genere
            );

            $nextNumber = $lastNumber + 1;

        } else {

            $nextNumber = 1;
        }

        $identifiant = $prefix . str_pad(
            $nextNumber,
            3,
            '0',
            STR_PAD_LEFT
        );

        // ==========================================
        // 5. CRÉATION DE L'APPLICATION
        // ==========================================

        $application = new Application();

        // Identifiant généré automatiquement
        $application->identifiant_genere = $identifiant;

        // Autres données validées
        foreach ($validated as $field => $value) {
            $application->{$field} = $value;
        }

        $application->save();

        // ==========================================
        // 6. REDIRECTION
        // ==========================================

        return redirect()
            ->route('appli.index')
            ->with(
                'success',
                'Application ' . $identifiant . ' ajoutée avec succès.'
            );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $application = Application::with([
            'applicationType',
            'server',
            'responsibleUser'
        ])->findOrFail($id);

        return view(
            'administration.applis.show',
            compact('application')
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $application = Application::findOrFail($id);

        $applicationTypes = ApplicationType::where('status', true)
            ->orderBy('name')
            ->get();

        $servers = Server::orderBy('name')->get();

        $users = User::orderBy('name')->get();

        return view(
            'administration.applis.edit',
            compact(
                'application',
                'applicationTypes',
                'servers',
                'users'
            )
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Nous gérerons la modification après
        // avoir terminé la création.

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $application = Application::findOrFail($id);

        $application->delete();

        return redirect()
            ->route('appli.index')
            ->with(
                'success',
                'Application supprimée avec succès.' );
    }
}