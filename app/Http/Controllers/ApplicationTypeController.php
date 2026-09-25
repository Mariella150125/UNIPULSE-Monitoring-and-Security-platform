<?php

namespace App\Http\Controllers;

use App\Models\ApplicationType;
use Illuminate\Http\Request;

class ApplicationTypeController extends Controller
{
    public function index()
    {
        $applicationTypes = ApplicationType::orderBy('name')->get();
        return view('layout.appli-type', compact('applicationTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:application_types,name',
            'description' => 'nullable|string',
        ]);

        ApplicationType::create($validated);

        return redirect()->route('application-types.index')->with('success', 'Type d’application ajouté avec succès.');
    }

    public function update(Request $request, ApplicationType $applicationType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:application_types,name,' . $applicationType->id,
            'description' => 'nullable|string',
        ]);

        $applicationType->update($validated);
        return redirect()->route('application-types.index')->with('success', 'Type d’application modifié avec succès.');
    }

    public function activate(ApplicationType $applicationType)
    {
        $applicationType->update(['status' => true]);
        return redirect()->route('application-types.index')->with('success', 'Type activé avec succès.');
    }

    public function deactivate(ApplicationType $applicationType)
    {
        $applicationType->update(['status' => false]);
        return redirect()->route('application-types.index')->with('success', 'Type désactivé avec succès.');
    }

    public function destroy(ApplicationType $applicationType)
    {
        $applicationType->delete();
        return redirect()->route('application-types.index')->with('success', 'Type d’application supprimé définitivement.');
    }
}