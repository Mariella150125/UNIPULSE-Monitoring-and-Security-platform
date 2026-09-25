<?php

namespace App\Http\Controllers;

use App\Models\ServerGroup;
use Illuminate\Http\Request;

class ServerGroupController extends Controller
{
    public function index()
    {
        $serverGroups = ServerGroup::with('servers')->orderBy('name')->get();
        return view('administration.servers.server-group', compact('serverGroups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:server_groups,name',
            'description' => 'nullable|string',
        ]);

        ServerGroup::create($validated);
        return redirect()->route('server-groups.index')->with('success', 'Groupe de serveurs ajouté avec succès.');
    }

    public function update(Request $request, ServerGroup $serverGroup)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:server_groups,name,' . $serverGroup->id,
            'description' => 'nullable|string',
        ]);

        $serverGroup->update($validated);
        return redirect()->route('server-groups.index')->with('success', 'Groupe modifié avec succès.');
    }

    public function destroy(ServerGroup $serverGroup)
    {
        $serverGroup->delete();
        return redirect()->route('server-groups.index')->with('success', 'Groupe supprimé avec succès.');
    }
}