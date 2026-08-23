<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Server;
use App\Models\ServerGroup;
class ServerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    
        $servers = Server::with('group')
            ->orderBy('created_at', 'desc')
            ->get();
        $groups = ServerGroup::orderBy('name')->get();

        //$totalServers = User::count();
        //$activeServers = User::where('status' , 'actif')->count();
        //$inactiveUsers = User::where('status' , 'inactif')->count();
        //$admins = User::where('role', 'Admin')->count();
        

        return view('administration.servers.server', compact(
            'servers' , 
            'groups',
            ));
    }

    /**
     * Show the form for creating a new resource.
     */

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation des données reçues
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'hostname' => 'required|string|max:255|unique:servers,hostname',
            'ip_address' => 'required|ip',
            'port' => 'nullable|integer|min:1|max:65535',

            'os' => 'required|string|max:100',
            'os_version' => 'nullable|string|max:100',

            'environment' => 'required|string|max:100',
            'department' => 'nullable|string|max:100',

            'description' => 'nullable|string',

            'tags' => 'nullable|array',

            'group_id' => 'nullable|exists:server_groups,id',
        ]);

        $server = Server::create($validated);

        return redirect()
            ->route('servers.index')
            ->with('success', 'Serveur ajouté avec succès.');

    }

    /**
     * Display the specified resource.
     */
    public function show(Server $server)
    {
        return view('administration.servers.show', compact('server'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Server $server)
    {
        $groups = ServerGroup::orderBy('name')->get();

        return view('administration.servers.edit', compact('server', 'groups'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Server $server)
    {

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Server $server)
    {
        
    }
    
}
