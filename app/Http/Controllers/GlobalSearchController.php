<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Server;
use App\Models\User;
use App\Models\Alert;
use App\Models\Log;
use App\Models\Report;
use App\Models\Connector;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function index(Request $request)
    {
        $query = trim($request->input('q', ''));

        $applications = collect();
        $servers = collect();
        $users = collect();
        $alerts = collect();
        $logs = collect();
        $reports = collect();
        $connectors = collect();

        if ($query !== '') {
            $applications = Application::with('applicationType')
                ->where(function ($q) use ($query) {
                    $q->where('name', 'ILIKE', "%{$query}%")
                      ->orWhere('identifiant_genere', 'ILIKE', "%{$query}%");
                })
                ->limit(10)->get();

            $servers = Server::where(function ($q) use ($query) {
                    $q->where('name', 'ILIKE', "%{$query}%")
                      ->orWhere('hostname', 'ILIKE', "%{$query}%")
                      ->orWhere('ip_address', 'ILIKE', "%{$query}%");
                })->limit(10)->get();

            $users = User::where(function ($q) use ($query) {
                    $q->where('name', 'ILIKE', "%{$query}%")
                      ->orWhere('email', 'ILIKE', "%{$query}%");
                })->limit(10)->get();

            $alerts = Alert::where(function ($q) use ($query) {
                    $q->where('title', 'ILIKE', "%{$query}%")
                      ->orWhere('description', 'ILIKE', "%{$query}%");
                })->latest()->limit(10)->get();

            $logs = Log::where('message', 'ILIKE', "%{$query}%")
                ->latest('created_at')->limit(10)->get();
                
            $reports = Report::where('name', 'ILIKE', "%{$query}%")->limit(10)->get();
            $connectors = Connector::where('name', 'ILIKE', "%{$query}%")->limit(10)->get();
        }

        return view('layout.search', compact(
            'query', 'applications', 'servers', 'users', 'alerts', 'logs', 'reports', 'connectors'
        ));
    }
}