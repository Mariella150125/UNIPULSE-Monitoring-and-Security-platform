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
                            {{ $log->resource_type }} 
                            @if($log->resource_id) #{{ $log->resource_id }} @endif
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

    <div>
        {{ $logs->links() }}
    </div>
    
    <a href="{{ route('settings') }}" class="btn btn-cancel">
        <i class="fa-solid fa-arrow-left"></i> Retour aux paramètres
    </a>
</div>

@endsection