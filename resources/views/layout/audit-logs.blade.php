@extends('layout.app')

@section('content')

<div class="page-title">
    <h1>Journaux d'audit</h1>
    <p>Historique des actions et tentatives de connexion</p>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="search-bar">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" placeholder="Rechercher une action, une IP..." data-filter-table="audit-table">
        </div>
    </div>

    <table class="server-table" id="audit-table">
        <thead>
            <tr>
                <th>Date & Heure</th>
                <th>Utilisateur</th>
                <th>Action</th>
                <th>Ressource</th>
                <th>Adresse IP</th>
                <th>Résultat</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($logs as $log)
                <tr>
                    <td class="text-sm">
                        {{ $log->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="font-medium">
                        {{ $log->user?->name ?? 'Inconnu' }}
                    </td>
                    <td>
                        <span class="event-badge">
                            {{ str_replace('_', ' ', $log->action) }}
                        </span>
                    </td>
                    <td class="text-sm text-muted">
                        @if($log->resource_type)
                            <strong>{{ $log->resource_type }}</strong> 
                            @if($log->resource_id) #{{ $log->resource_id }} @endif
                            <br>
                            <small>{{ $log->details ?? '—' }}</small>
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-sm text-muted">
                        {{ $log->ip_address }}
                    </td>
                    <td>
                        <span class="status-badge {{ $log->is_success ? 'success' : 'timeout' }}">
                            <span class="status-dot"></span>
                            {{ $log->is_success ? 'Succès' : 'Échec' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="table-empty">Aucun journal d'audit trouvé.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="custom-pagination">
        {{ $logs->links() }}
    </div>
    
    <style>
        /* --- CSS POUR LA PAGINATION --- */
        .custom-pagination nav {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        .custom-pagination nav a,
        .custom-pagination nav span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            padding: 0 12px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            border: 1px solid var(--border-color, #ccc);
            color: var(--text-dark, #333);
            background-color: var(--panel-bg, #fff);
            transition: all 0.2s ease;
        }
        .custom-pagination nav a:hover {
            background-color: var(--page-bg, #f8f8f6);
            border-color: var(--dark-teal, #1d4a40);
        }
        /* Page active */
        .custom-pagination nav span[aria-current="page"] {
            background-color: var(--dark-teal, #1d4a40);
            color: white;
            border-color: var(--dark-teal, #1d4a40);
        }
        /* Boutons désactivés (précédent/suivant quand on est au bout) */
        .custom-pagination nav span[aria-disabled="true"] {
            opacity: 0.5;
            cursor: not-allowed;
            color: var(--text-muted, #999);
        }
        /* Fix pour les flèches SVG de Tailwind */
        .custom-pagination svg {
            width: 14px;
            height: 14px;
            display: inline-block;
        }
    </style>
    
    <a href="{{ route('settings') }}" class="btn btn-cancel">
        <i class="fa-solid fa-arrow-left"></i> Retour aux paramètres
    </a>
</div>

@endsection