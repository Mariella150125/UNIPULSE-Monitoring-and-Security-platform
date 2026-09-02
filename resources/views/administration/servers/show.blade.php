@extends('layout.app')

@section('content')

<div class="page-title">
    <h1>Détails du serveur</h1>
</div>

<div class="entity-details">

    <div class="entity-details-header">
        <div>
            <h2>{{ $server->hostname }}</h2>
            <p>{{ $server->name }}</p>
        </div>
        <div style="display:flex;gap:10px;">
            <a href="{{ route('server.index') }}" class="btn btn-cancel">
                <i class="fa-solid fa-arrow-left"></i>
                Retour
            </a>
            
        </div>
    </div>

    <div class="entity-details-body">
        <div class="details-grid">

            <div class="detail-item">
                <span class="detail-label">Nom</span>
                <span class="detail-value">{{ $server->name }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Hostname</span>
                <span class="detail-value">{{ $server->hostname }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Adresse IP</span>
                <span class="detail-value">{{ $server->ip_address }}{{ $server->port ? ':' . $server->port : '' }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Système d'exploitation</span>
                <span class="detail-value">{{ $server->os }} {{ $server->os_version ? '(' . $server->os_version . ')' : '' }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Environnement</span>
                <span class="detail-value">{{ $server->environment }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Département</span>
                <span class="detail-value">{{ $server->department ?? '—' }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Groupe</span>
                <span class="detail-value">{{ $server->group?->name ?? 'Aucun groupe' }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Description</span>
                <span class="detail-value">{{ $server->description ?? '—' }}</span>
            </div>

            @if ($server->tags)
                <div class="detail-item" style="grid-column: span 2;">
                    <span class="detail-label">Tags</span>
                    <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:4px;">
                        @foreach ($server->tags as $tag)
                            <span class="env-badge">{{ $tag }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            @php
                $dotClass = match($server->global_status) {
                    'healthy'  => 'online',
                    'critical' => 'offline',
                    'warning'  => 'warning',
                    default    => '',
                };
                $dotStyle = $server->global_status === 'unknown' ? 'background:var(--text-muted);' : '';
                $statusLabel = match($server->global_status) {
                    'healthy'  => 'En bonne santé',
                    'critical' => 'Critique',
                    'warning'  => 'Warning',
                    default    => 'Inconnu',
                };
            @endphp

            <div class="detail-item">
                <span class="detail-label">Statut</span>
                <span class="detail-value">
                    <span class="status-dot {{ $dotClass }}" style="{{ $dotStyle }}"></span>
                    {{ $statusLabel }}
                </span>
                @if ($server->global_status_updated_at)
                    <span style="font-size:12px;color:var(--text-muted);margin-top:4px;">
                        Depuis {{ $server->global_status_updated_at->diffForHumans() }}
                    </span>
                @endif
            </div>

            <div class="detail-item">
                <span class="detail-label">Connecteur Prometheus</span>
                <span class="detail-value">{{ $server->prometheus_instance ?? 'Non configuré' }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Agent Wazuh</span>
                <span class="detail-value">{{ $server->wazuh_agent_id ?? 'Non configuré' }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Agent Wazuh — Statut</span>
                <span class="detail-value">{{ $server->wazuh_agent_status }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Créé le</span>
                <span class="detail-value">{{ $server->created_at->format('d/m/Y à H:i') }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Modifié le</span>
                <span class="detail-value">{{ $server->updated_at->format('d/m/Y à H:i') }}</span>
            </div>

        </div>
    </div>

</div>

@if ($server->applications->isNotEmpty())
    <div style="margin-top:32px;">
        <div class="entity-details">
            <div class="entity-details-header">
                <h2 style="margin:0;font-size:16px;">Applications hébergées ({{ $server->applications->count() }})</h2>
            </div>
            <table class="server-table">
                <thead>
                    <tr>
                        <th>Application</th>
                        <th>URL</th>
                        <th>Environnement</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($server->applications as $app)
                        <tr>
                            <td>{{ $app->name }}</td>
                            <td style="font-size:13px;">{{ $app->url ?? '—' }}</td>
                            <td>
                                <span class="env-badge">{{ $app->environment }}</span>
                            </td>
                            <td>
                                <span class="status-dot {{ $app->status === 'active' ? 'online' : 'offline' }}"></span>
                                {{ $app->status === 'active' ? 'Active' : 'Inactive' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@endsection