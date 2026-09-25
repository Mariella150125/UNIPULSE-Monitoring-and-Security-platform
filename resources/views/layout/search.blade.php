@extends('layout.app')

@section('content')

<div class="page-title">
    <a href="{{ route('dashboard') }}" class="btn btn-cancel">
        <i class="fa-solid fa-arrow-left"></i> Retour au tableau de bord
    </a>
    <h1>Résultats de recherche</h1>
    <p>Terme recherché : <strong>{{ $query }}</strong></p>
</div>

@if($query === '')
    <div class="panel">
        <div style="text-align: center; padding: 40px; color: var(--text-muted);">
            <i class="fa-solid fa-magnifying-glass" style="font-size: 32px; margin-bottom: 10px; display: block;"></i>
            Entrez un terme dans la barre de recherche.
        </div>
    </div>
@else

    {{-- APPLICATIONS --}}
    @if($applications->count())
    <div class="panel" style="margin-bottom: 24px;">
        <div class="panel-header"><p>Applications ({{ $applications->count() }})</p></div>
        <table class="server-table">
            <thead>
                <tr><th>Nom</th><th>Type</th><th>Statut</th><th>Action</th></tr>
            </thead>
            <tbody>
                @foreach($applications as $app)
                <tr>
                    <td><strong>{{ $app->name }}</strong></td>
                    <td>{{ $app->applicationType?->name ?? 'N/A' }}</td>
                    <td><span class="badge">{{ ucfirst($app->status) }}</span></td>
                    <td><a href="{{ route('monitoring.application.show', $app->id) }}" class="usr-btn-1" style="padding: 5px 10px; font-size: 12px;">Voir</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- SERVEURS --}}
    @if($servers->count())
    <div class="panel" style="margin-bottom: 24px;">
        <div class="panel-header"><p>Serveurs ({{ $servers->count() }})</p></div>
        <table class="server-table">
            <thead>
                <tr><th>Nom</th><th>IP</th><th>Statut</th><th>Action</th></tr>
            </thead>
            <tbody>
                @foreach($servers as $server)
                <tr>
                    <td><strong>{{ $server->name }}</strong></td>
                    <td>{{ $server->ip_address }}</td>
                    <td><span class="status-dot online"></span> {{ ucfirst($server->global_status) }}</td>
                    <td><a href="{{ route('server.show', $server->id) }}" class="usr-btn-1" style="padding: 5px 10px; font-size: 12px;">Voir</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- UTILISATEURS --}}
    @if($users->count())
    <div class="panel" style="margin-bottom: 24px;">
        <div class="panel-header"><p>Utilisateurs ({{ $users->count() }})</p></div>
        <table class="server-table">
            <thead>
                <tr><th>Nom</th><th>Email</th><th>Action</th></tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td><strong>{{ $user->name }}</strong></td>
                    <td>{{ $user->email }}</td>
                    <td><a href="{{ route('users.show', $user->id) }}" class="usr-btn-1" style="padding: 5px 10px; font-size: 12px;">Voir</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- ALERTES --}}
    @if($alerts->count())
    <div class="panel" style="margin-bottom: 24px;">
        <div class="panel-header"><p>Alertes ({{ $alerts->count() }})</p></div>
        <table class="server-table">
            <thead>
                <tr><th>Titre</th><th>Priorité</th><th>Date</th><th>Action</th></tr>
            </thead>
            <tbody>
                @foreach($alerts as $alert)
                <tr>
                    <td><strong>{{ $alert->title }}</strong></td>
                    <td><span class="badge badge-critical">{{ ucfirst($alert->priority) }}</span></td>
                    <td>{{ $alert->created_at->format('d/m/Y H:i') }}</td>
                    <td><a href="{{ route('alerts.show', $alert->id) }}" class="usr-btn-1" style="padding: 5px 10px; font-size: 12px;">Voir</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- LOGS --}}
    @if($logs->count())
    <div class="panel" style="margin-bottom: 24px;">
        <div class="panel-header"><p>Logs ({{ $logs->count() }})</p></div>
        <table class="server-table">
            <thead>
                <tr><th>Niveau</th><th>Source</th><th>Message</th><th>Date</th></tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                <tr>
                    <td><span class="badge {{ $log->level == 'ERROR' ? 'badge-critical' : '' }}">{{ $log->level }}</span></td>
                    <td>{{ $log->source }}</td>
                    <td>{{ $log->message }}</td>
                    <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- RAPPORTS --}}
    @if($reports->count())
    <div class="panel" style="margin-bottom: 24px;">
        <div class="panel-header"><p>Rapports ({{ $reports->count() }})</p></div>
        <table class="server-table">
            <thead>
                <tr><th>Nom</th><th>Type</th><th>Date</th><th>Action</th></tr>
            </thead>
            <tbody>
                @foreach($reports as $report)
                <tr>
                    <td><strong>{{ $report->name }}</strong></td>
                    <td><span class="badge">{{ ucfirst($report->type) }}</span></td>
                    <td>{{ $report->created_at->format('d/m/Y H:i') }}</td>
                    <td><a href="{{ route('reports.download', $report->id) }}" class="usr-btn-1" style="padding: 5px 10px; font-size: 12px;">Télécharger</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- CONNECTEURS --}}
    @if($connectors->count())
    <div class="panel" style="margin-bottom: 24px;">
        <div class="panel-header"><p>Connecteurs ({{ $connectors->count() }})</p></div>
        <table class="server-table">
            <thead>
                <tr><th>Nom</th><th>Type</th><th>Statut</th><th>Action</th></tr>
            </thead>
            <tbody>
                @foreach($connectors as $connector)
                <tr>
                    <td><strong>{{ $connector->name }}</strong></td>
                    <td><span class="badge">{{ ucfirst($connector->type) }}</span></td>
                    <td><span class="status-dot online"></span> {{ ucfirst($connector->status) }}</td>
                    <td><a href="{{ route('connectors.show', $connector->id) }}" class="usr-btn-1" style="padding: 5px 10px; font-size: 12px;">Voir</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- AUCUN RÉSULTAT --}}
    @if(
        !$applications->count() &&
        !$servers->count() &&
        !$users->count() &&
        !$alerts->count() &&
        !$logs->count() &&
        !$reports->count() &&
        !$connectors->count()
    )
        <div class="panel">
            <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                <i class="fa-solid fa-circle-exclamation" style="font-size: 32px; margin-bottom: 10px; display: block;"></i>
                Aucun résultat trouvé pour <strong>{{ $query }}</strong>.
            </div>
        </div>
    @endif

@endif

@endsection