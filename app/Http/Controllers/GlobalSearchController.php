<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Server;
use App\Models\User;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function index(Request $request)
    {
        $query = trim($request->input('q', ''));

        $applications = collect();
        $servers = collect();
        $users = collect();

        if ($query !== '') {

            $applications = Application::with('applicationType')
                ->where(function ($q) use ($query) {
                    $q->where('name', 'ILIKE', "%{$query}%")
                      ->orWhere('identifiant_genere', 'ILIKE', "%{$query}%");
                })
                ->limit(10)
                ->get();

            $servers = Server::where(function ($q) use ($query) {
                    $q->where('name', 'ILIKE', "%{$query}%");
                })
                ->limit(10)
                ->get();

            $users = User::where(function ($q) use ($query) {
                    $q->where('name', 'ILIKE', "%{$query}%")
                      ->orWhere('email', 'ILIKE', "%{$query}%");
                })
                ->limit(10)
                ->get();
        }

        return view(
            'layout.search',
            compact(
                'query',
                'applications',
                'servers',
                'users'
            )
        );
    }
}