<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\ApplicationType;
use App\Models\Server;
use App\Models\User;
use App\Models\ApplicationAvailability;
use Illuminate\Support\Facades\Auth;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        $query = Application::with(['applicationType', 'server', 'responsibleUser']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('identifiant_genere', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('url', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('environment')) {
            $query->where('environment', $request->environment);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $applications = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        $applicationTypes = ApplicationType::where('status', true)->orderBy('name')->get();
        $servers = Server::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $activeApplications = Application::where('status', 'active')->count();

        $environmentStats = Application::selectRaw('environment, COUNT(*) as total')
            ->groupBy('environment')->get();

        $availabilityStats = ApplicationAvailability::query()
            ->where('checked_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(checked_at) as date')
            ->selectRaw('AVG(CASE WHEN is_available = true THEN 100 ELSE 0 END) as availability')
            ->groupByRaw('DATE(checked_at)')
            ->orderBy('date')
            ->get();

        return view('administration.applis.appli', compact(
            'applications', 'applicationTypes', 'servers', 'users',
            'activeApplications', 'environmentStats', 'availabilityStats'
        ));
    }

    public function create()
    {
        return view('administration.applis.create', [
            'applicationTypes' => ApplicationType::where('status', true)->orderBy('name')->get(),
            'servers' => Server::orderBy('name')->get(),
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'url' => 'nullable|url|max:255',
            'language' => 'nullable|string|max:100',
            'framework' => 'nullable|string|max:100',
            'version' => 'nullable|string|max:50',
            'application_type_id' => ['required', 'exists:application_types,id'],
            'environment' => ['required', 'in:development,test,staging,production'],
            'database_used' => 'nullable|string|max:100',
            'client_name' => 'nullable|string|max:255',
            'tags' => 'nullable',
            'status' => ['nullable', 'in:development,testing,staging,active,maintenance,suspended,retired'],
            'is_hosted' => 'required|boolean',
            'server_id' => ['nullable', 'exists:servers,id'],
            'port' => 'nullable|integer',
            'deployment_path' => 'nullable|string|max:500',
            'responsible_user_id' => ['nullable', 'exists:users,id'],
            'monitoring_enabled' => 'nullable|boolean',
            'prometheus_job' => 'nullable|string|max:255',
            'metrics_endpoint' => 'nullable|string|max:255',
            'scrape_interval' => 'nullable|string|max:50',
            'url_health_check' => 'nullable|url|max:500',
            'wazuh_enabled' => 'nullable|boolean',
            'criticality' => ['nullable', 'in:low,medium,high,critical'],
        ]);

        if ($validated['is_hosted'] && empty($validated['server_id'])) {
            return back()->withErrors(['server_id' => 'Un serveur est obligatoire pour une application hébergée.'])->withInput();
        }

        if (!$validated['is_hosted']) {
            $validated['server_id'] = null;
            $validated['port'] = null;
            $validated['deployment_path'] = null;
        }

        $validated['status'] = $validated['status'] ?? 'planned';
        $validated['monitoring_enabled'] = $validated['monitoring_enabled'] ?? false;
        $validated['wazuh_enabled'] = $validated['wazuh_enabled'] ?? false;
        $validated['hosting_type'] = $validated['is_hosted'] ? 'hosted' : 'non_hosted';

        $prefix = $validated['is_hosted'] ? 'APN-H-' : 'APN-NH-';
        $lastApplication = Application::where('identifiant_genere', 'like', $prefix . '%')->orderByDesc('id')->first();
        $nextNumber = $lastApplication ? ((int) str_replace($prefix, '', $lastApplication->identifiant_genere)) + 1 : 1;
        $identifiant = $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        $application = new Application();
        $application->identifiant_genere = $identifiant;

        foreach ($validated as $field => $value) {
            $application->{$field} = $value;
        }

        $application->save();

        return redirect()->route('appli.index')->with('success', 'Application ' . $identifiant . ' ajoutée avec succès.');
    }

    public function show(string $id)
    {
        $application = Application::with(['applicationType', 'server', 'responsibleUser'])->findOrFail($id);
        return view('administration.applis.show', compact('application'));
    }

    public function edit(string $id)
    {
        $application = Application::findOrFail($id);
        return view('administration.applis.edit', [
            'application' => $application,
            'applicationTypes' => ApplicationType::where('status', true)->orderBy('name')->get(),
            'servers' => Server::orderBy('name')->get(),
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $application = Application::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'url' => 'nullable|url|max:255',
            'language' => 'nullable|string|max:100',
            'framework' => 'nullable|string|max:100',
            'version' => 'nullable|string|max:50',
            'application_type_id' => ['required', 'exists:application_types,id'],
            'environment' => ['required', 'in:development,test,staging,production'],
            'database_used' => 'nullable|string|max:100',
            'client_name' => 'nullable|string|max:255',
            'tags' => 'nullable',
            'status' => ['nullable', 'in:development,testing,staging,active,maintenance,suspended,retired'],
            'is_hosted' => 'required|boolean',
            'server_id' => ['nullable', 'exists:servers,id'],
            'port' => 'nullable|integer',
            'deployment_path' => 'nullable|string|max:500',
            'responsible_user_id' => ['nullable', 'exists:users,id'],
            'monitoring_enabled' => 'nullable|boolean',
            'prometheus_job' => 'nullable|string|max:255',
            'metrics_endpoint' => 'nullable|string|max:255',
            'scrape_interval' => 'nullable|string|max:50',
            'url_health_check' => 'nullable|url|max:500',
            'wazuh_enabled' => 'nullable|boolean',
            'criticality' => ['nullable', 'in:low,medium,high,critical'],
        ]);

        if ($validated['is_hosted'] && empty($validated['server_id'])) {
            return back()->withErrors(['server_id' => 'Un serveur est obligatoire pour une application hébergée.'])->withInput();
        }

        if (!$validated['is_hosted']) {
            $validated['server_id'] = null;
            $validated['port'] = null;
            $validated['deployment_path'] = null;
        }

        $validated['monitoring_enabled'] = $validated['monitoring_enabled'] ?? false;
        $validated['wazuh_enabled'] = $validated['wazuh_enabled'] ?? false;
        $validated['hosting_type'] = $validated['is_hosted'] ? 'hosted' : 'non_hosted';

        $application->update($validated);

        return redirect()->route('appli.index')->with('success', 'Application modifiée avec succès.');
    }
    /**
     * Page de confirmation de suppression.
     */
    public function delete($id)
    {
        $application = Application::findOrFail($id);
        return view('administration.applis.delete', compact('application'));
    }
    public function destroy(string $id)
    {
        $application = Application::findOrFail($id)->delete();
       // if ($application->created_by !== Auth::id()) abort(403);
        //$this->service->delete($application);
        return redirect()->route('appli.index')->with('success', 'Application supprimée avec succès.');
    }
}