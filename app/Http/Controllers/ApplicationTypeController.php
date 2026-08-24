<?php

namespace App\Http\Controllers;

use App\Models\ApplicationType;
use Illuminate\Http\Request;

class ApplicationTypeController extends Controller
{
    /**
     * Afficher les types d'applications.
     */
    public function index()
    {
        $applicationTypes = ApplicationType::orderBy('name')->get();

        return view(
            'layout.appli-type',
            compact('applicationTypes')
        );
    }

    /**
     * Ajouter un type d'application.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:application_types,name',
            'description' => 'nullable|string',
        ]);

        ApplicationType::create($validated);

        return redirect()
            ->route('application-types.index')
            ->with('success', 'Type d’application ajouté avec succès.');
    }

    /**
     * Modifier un type d'application.
     */
    public function update(Request $request, ApplicationType $applicationType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:application_types,name,' . $applicationType->id,
            'description' => 'nullable|string',
            'status' => 'boolean',
        ]);

        $applicationType->update($validated);

        return redirect()
            ->route('administration.applis.appli')
            ->with('success', 'Type d’application modifié avec succès.');
    }

    /**
     * Désactiver un type d'application.
     */
    public function destroy(ApplicationType $applicationType)
    {
        $applicationType->update([
            'status' => false,
        ]);

        return redirect()
            ->route('application-types.index')
            ->with('success', 'Type d’application désactivé avec succès.');
    }
}