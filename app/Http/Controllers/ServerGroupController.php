<?php

namespace App\Http\Controllers;

use App\Models\ServerGroup;
use Illuminate\Http\Request;

class ServerGroupController extends Controller
{
    /**
     * Afficher les groupes de serveurs.
     */
    public function index()
    {
        $serverGroups = ServerGroup::with('servers')
            ->orderBy('name')
            ->get();

        return view(
            'administration.servers.server-group',
            compact('serverGroups')
        );
    }

    /**
     * Ajouter un groupe de serveurs.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:server_groups,name',
            'description' => 'nullable|string',
        ]);

        ServerGroup::create($validated);

        return redirect()
            ->route('server-groups.index')
            ->with(
                'success',
                'Groupe de serveurs ajouté avec succès.'
            );
    }

    /**
     * Modifier un groupe de serveurs.
     */
    public function update(
        Request $request,
        ServerGroup $serverGroup
    ) {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:server_groups,name,' . $serverGroup->id,
            'description' => 'nullable|string',
        ]);

        $serverGroup->update($validated);

        return redirect()
            ->route('server-groups.index')
            ->with(
                'success',
                'Groupe de serveurs modifié avec succès.'
            );
    }
}