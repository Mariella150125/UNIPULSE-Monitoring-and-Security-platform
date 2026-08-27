@extends('layout.app')

@section('content')

<div class="page-title">
    <h1>Détails du serveur</h1>
    <p>{{ $server->hostname }}</p>
</div>

<div class="details-card">

    <div>
        <strong>Nom</strong>
        <p>{{ $server->name }}</p>
    </div>

    <div>
        <strong>Hostname</strong>
        <p>{{ $server->hostname }}</p>
    </div>

    <div>
        <strong>Adresse IP</strong>
        <p>{{ $server->ip_address }}{{ $server->port ? ':' . $server->port : '' }}</p>
    </div>

    <div>
        <strong>Système d'exploitation</strong>
        <p>{{ $server->os }} {{ $server->os_version ? '(' . $server->os_version . ')' : '' }}</p>
    </div>

    <div>
        <strong>Environnement</strong>
        <p>{{ $server->environment }}</p>
    </div>

    <div>
        <strong>Département</strong>
        <p>{{ $server->department ?? '—' }}</p>
    </div>

    <div>
        <strong>Groupe</strong>
        <p>{{ $server->group?->name ?? 'Aucun groupe' }}</p>
    </div>

    <div>
        <strong>Description</strong>
        <p>{{ $server->description ?? '—' }}</p>
    </div>

    @if ($server->tags)
        <div>
            <strong>Tags</strong>
            <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:4px;">
                @foreach ($server->tags as $tag)
                    <span class="env-badge">{{ $tag }}</span>
                @endforeach
            </div>
        </div>
    @endif

    <div>
        <strong>Statut</strong>
        @php
            $dotClass = match($server->global_status) {
                'healthy' => 'online',
                'critical' => 'offline',
                'warning' => 'warning',
                default   => '',
            };
            $dotStyle = $server->global_status === 'unknown' ? 'background:var(--text-muted);' : '';
            $statusLabel = match($server->global_status) {
                'healthy' => 'En bonne santé',
                'critical' => 'Critique',
                'warning' => 'Warning',
                default   => 'Inconnu',
            };
        @endphp
        <p>
            <span class="status-dot {{ $dotClass }}" style="{{ $dotStyle }}"></span>
            {{ $statusLabel }}
        </p>
        @if ($server->global_status_updated_at)
            <p style="font-size:12px;color:var(--text-muted);margin-top:2px;">
                Depuis {{ $server->global_status_updated_at->diffForHumans() }}
            </p>
        @endif
    </div>

    <div>
        <strong>Connecteur Prometheus</strong>
        <p>{{ $server->prometheus_instance ?? 'Non configuré' }}</p>
    </div>

    <div>
        <strong>Agent Wazuh</strong>
        <p>{{ $server->wazuh_agent_id ?? 'Non configuré' }}</p>
    </div>

    <div>
        <strong>Agent Wazuh — Statut</strong>
        <p>{{ $server->wazuh_agent_status }}</p>
    </div>

    <div>
        <strong>Créé le</strong>
        <p>{{ $server->created_at->format('d/m/Y à H:i') }}</p>
    </div>

    <div>
        <strong>Modifié le</strong>
        <p>{{ $server->updated_at->format('d/m/Y à H:i') }}</p>
    </div>

</div>


{{-- Applications hébergées sur ce serveur --}}
@if ($server->applications->isNotEmpty())

    <div style="margin-top:32px;">

        <div class="page-title" style="margin-bottom:16px;">
            <h2 style="font-size:16px;">Applications hébergées ({{ $server->applications->count() }})</h2>
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

@endif


{{-- ACTIONS --}}
<div class="form-actions" style="margin-top:24px;">
    <a href="{{ route('server.index') }}" class="btn-cancel">Retour à la liste</a>
    <a href="{{ route('server.edit', $server) }}" class="usr-btn secondary">
        <i class="fa-solid fa-pen"></i> Modifier
    </a>
</div>


@endsection