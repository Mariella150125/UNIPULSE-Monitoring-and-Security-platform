<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceWindow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaintenanceWindowController extends Controller
{
    // Afficher la page avec le formulaire et le tableau
    public function index(): View
    {
        // On récupère toutes les maintenances (futures, en cours et passées)
        $maintenances = MaintenanceWindow::orderBy('start_time', 'desc')->get();
        $servers = \App\Models\Server::orderBy('name')->pluck('name');
        $applications = \App\Models\Application::orderBy('name')->pluck('name');
        return view('layout.settings.maintenance', compact('maintenances', 'servers' , 'applications'));
    }

    // Enregistrer une nouvelle maintenance
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'resource_type' => 'required|in:server,application',
            'resource_name' => 'required|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        MaintenanceWindow::create([
            'resource_type' => $request->resource_type,
            'resource_name' => $request->resource_name,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'is_active' => true,
        ]);

        return redirect()->route('settings.maintenance.index')->with('success', 'Fenêtre de maintenance planifiée avec succès.');
    }

    // Annuler une maintenance (la passer en is_active = false)
    public function destroy($id): RedirectResponse
    {
        $maintenance = MaintenanceWindow::findOrFail($id);
        $maintenance->update(['is_active' => false]);

        return redirect()->route('settings.maintenance.index')->with('success', 'Maintenance annulée.');
    }
}