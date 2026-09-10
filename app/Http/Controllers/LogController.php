<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Log;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $query = Log::with('application')->latest('created_at');

        // 1. Recherche plein texte (MF-32)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('message', 'ILIKE', "%{$search}%");
        }

        // 2. Filtre par niveau
        if ($request->filled('level') && $request->level !== 'ALL') {
            $query->where('level', $request->level);
        }

        // 3. Filtre par source (Nginx, MySQL, etc.)
        if ($request->filled('source')) {
            $query->where('source', 'ILIKE', "%{$request->source}%");
        }

        $logs = $query->paginate(50)->withQueryString();
        
        // Statistiques rapides pour la vue
        $stats = [
            'error' => Log::where('level', 'ERROR')->count(),
            'warning' => Log::where('level', 'WARNING')->count(),
        ];

        return view('monitoring.logs.index', compact('logs', 'stats'));
    }
}