@extends('layout.app')

@section('content')

<div class="page-title">
    <h1>Tester la connexion</h1>
    <p>{{ $connector->name }}</p>
</div>


{{-- INFOS DU CONNECTEUR --}}
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
        <p>{{ $connector->api_port ?? $connector->effective_port }}</p>
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
        <strong>Statut actuel</strong>
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
        </p>
    </div>

    <div>
        <strong>Dernière vérification</strong>
        <p>{{ $connector->last_check_at ? $connector->last_check_at->diffForHumans() : 'Jamais' }}</p>
    </div>

    @if ($connector->last_error_message)
        <div>
            <strong>Dernière erreur</strong>
            <p style="color:var(--c-red);">{{ $connector->last_error_message }}</p>
        </div>
    @endif

</div>


{{-- BOUTON TEST --}}
<div style="margin-top:24px;">

    <button
        type="button"
        class="usr-btn"
        id="test-btn"
        onclick="runTest('{{ $connector->id }}', this)"
    >
        <i class="fa-solid fa-plug"></i>
        Lancer le test
    </button>

</div>


{{-- RÉSULTAT --}}
<div id="test-result-card" style="display:none;margin-top:24px;">

    <div class="details-card" id="test-result-content">
        <!-- Rempli par JS -->
    </div>

</div>


{{-- DERNIERS LOGS --}}
<div style="margin-top:32px;">

    <div class="page-title" style="margin-bottom:16px;">
        <h2 style="font-size:16px;">Derniers tests</h2>
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
    <a href="{{ route('connectors.show', $connector) }}" class="btn-cancel">Retour au détail</a>
    <a href="{{ route('connectors.index') }}" class="btn-cancel">Retour à la liste</a>
</div>


@endsection