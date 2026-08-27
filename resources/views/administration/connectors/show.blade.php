@extends('layout.app')

@section('content')

<div class="page-title">
    <h1>Détails du connecteur</h1>
    <p>{{ $connector->name }}</p>
</div>

<div class="details-card">

    <div>
        <strong>Type</strong>
        <p>
            @if ($connector->type === 'prometheus')
                <i class="fa-solid fa-chart-line"></i> Prometheus
            @else
                <i class="fa-solid fa-shield-halved"></i> Wazuh
            @endif
        </p>
    </div>

    <div>
        <strong>URL</strong>
        <p>{{ $connector->full_url }}</p>
    </div>

    <div>
        <strong>Port</strong>
        <p>{{ $connector->api_port ?? 'Déduit de l\'URL (' . $connector->effective_port . ')' }}</p>
    </div>

    <div>
        <strong>Identifiant</strong>
        <p>{{ $connector->auth_username ?? 'Aucun' }}</p>
    </div>

    <div>
        <strong>Mot de passe</strong>
        <p>{{ $connector->has_password ? 'Configuré' : 'Aucun' }}</p>
    </div>

    <div>
        <strong>Statut</strong>
        <p>
            @php
                $dotClass = match($connector->status) {
                    'connected'    => 'online',
                    'error'        => 'offline',
                    default        => '',
                };
                $dotStyle = $connector->status === 'never_tested' ? 'background:var(--text-muted);' : '';
                $statusLabel = match($connector->status) {
                    'connected'    => 'Connecté',
                    'error'        => 'Erreur',
                    default        => 'Jamais testé',
                };
            @endphp
            <span class="status-dot {{ $dotClass }}" style="{{ $dotStyle }}"></span>
            {{ $statusLabel }}

            @if ($connector->is_prolonged_failure)
                <i class="fa-solid fa-triangle-exclamation" style="color:var(--c-red);font-size:12px;margin-left:6px;" title="Échec prolongé"></i>
            @endif
        </p>
    </div>

    <div>
        <strong>Dernière vérification</strong>
        <p>{{ $connector->last_check_at ? $connector->last_check_at->diffForHumans() : 'Jamais' }}</p>
    </div>

    <div>
        <strong>Dernier succès</strong>
        <p>{{ $connector->last_success_at ? $connector->last_success_at->diffForHumans() : 'Jamais' }}</p>
    </div>

    @if ($connector->last_error_message)
        <div>
            <strong>Dernière erreur</strong>
            <p style="color:var(--c-red);">{{ $connector->last_error_message }}</p>
        </div>
    @endif

    <div>
        <strong>Configuré par</strong>
        <p>{{ $connector->createdBy->name ?? '—' }}</p>
    </div>

    <div>
        <strong>Créé le</strong>
        <p>{{ $connector->created_at->format('d/m/Y H:i') }}</p>
    </div>

    <div>
        <strong>Modifié le</strong>
        <p>{{ $connector->updated_at->format('d/m/Y H:i') }}</p>
    </div>

    @if ($connector->extra_config)
        <div>
            <strong>Configuration avancée</strong>
            <pre style="background:var(--input-bg);padding:10px;border-radius:6px;font-size:13px;overflow-x:auto;">{{ json_encode($connector->extra_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    @endif

</div>


{{-- TEST DE CONNEXION --}}
<div style="margin-top:24px;">

    <button
        type="button"
        class="usr-btn"
        id="test-btn"
        onclick="testConnector('{{ $connector->id }}', this)"
    >
        <i class="fa-solid fa-plug"></i>
        Tester la connexion
    </button>

    <span id="test-result" style="margin-left:12px;font-size:13px;"></span>

</div>


{{-- HISTORIQUE --}}
<div style="margin-top:32px;">

    <div class="page-title" style="margin-bottom:16px;">
        <h2 style="font-size:16px;">Historique des connexions</h2>
        <p style="font-size:13px;color:var(--text-muted);">20 derniers tests</p>
    </div>

    <div class="connector-history">

        @forelse ($logs as $log)

            <div class="history-item">
                <span class="status-dot {{ $log->success ? 'online' : 'offline' }}"></span>
                <div>
                    <strong>
                        {{ $log->success ? 'Connexion réussie' : 'Échec de connexion' }}
                        @if ($log->duration_ms)
                            <span style="font-weight:400;color:var(--text-muted);font-size:11px;">({{ $log->duration_ms }} ms)</span>
                        @endif
                    </strong>
                    <p>
                        {{ $log->executed_at->diffForHumans() }}
                        @if (!$log->success && $log->error_message)
                            — {{ Str::limit($log->error_message, 100) }}
                        @endif
                    </p>
                </div>
            </div>

        @empty

            <div class="history-item">
                <div><p style="color:var(--text-muted);">Aucun historique.</p></div>
            </div>

        @endforelse

    </div>

</div>


{{-- ACTIONS --}}
<div class="form-actions" style="margin-top:24px;">
    <a href="{{ route('connectors.index') }}" class="btn-cancel">Retour</a>
    <a href="{{ route('connectors.edit', $connector) }}" class="usr-btn secondary">
        <i class="fa-solid fa-pen"></i> Modifier
    </a>
</div>


@endsection

