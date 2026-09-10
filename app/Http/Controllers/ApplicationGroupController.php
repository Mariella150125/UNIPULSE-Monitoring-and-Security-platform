<?php

namespace App\Http\Controllers;

use App\Models\ApplicationGroup;
use Illuminate\Http\Request;

class ApplicationGroupController extends Controller
{
    public function index()
    {
        $groups = ApplicationGroup::withCount('applications')->orderBy('name')->get();
        return view('administration.applis.appli-group', compact('groups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:application_groups,name',
            'description' => 'nullable|string',
        ]);

        ApplicationGroup::create($validated);

        return redirect()->back()->with('success', 'Groupe d\'applications ajouté avec succès.');
    }

    public function update(Request $request, ApplicationGroup $group)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:application_groups,name,' . $group->id,
            'description' => 'nullable|string',
        ]);

        $group->update($validated);

        return redirect()->back()->with('success', 'Groupe modifié avec succès.');
    }

    public function destroy(ApplicationGroup $group)
    {
        $group->delete();
        return redirect()->back()->with('success', 'Groupe supprimé avec succès.');
    }
}