@extends('layout.app')

@section('content')

<div class="main-content">
<div class="dashboard-content">

<div class="page-title">
    <h1>Tester la connexion</h1>
    <p>{{ $connector->name }}</p>
</div>

{{-- INFOS DU CONNECTEUR --}}
<div class="entity-details">

    <div class="entity-details-header">
        <div>
            <h2>{{ $connector->name }}</h2>
            <p>
                @if ($connector->type === 'prometheus')
                    <i class="fa-solid fa-chart-line"></i> Prometheus
                @else
                    <i class="fa-solid fa-shield-halved"></i> Wazuh
                @endif
            </p>
        </div>
        <a href="{{ route('connectors.show', $connector) }}" class="btn btn-cancel">
            <i class="fa-solid fa-arrow-left"></i>
            Retour au détail
        </a>
    </div>

    <div class="entity-details-body">
        <div class="details-grid">

            <div class="detail-item">
                <span class="detail-label">Type</span>
                <span class="detail-value">
                    @if ($connector->type === 'prometheus')
                        <i class="fa-solid fa-chart-line"></i> Prometheus
                    @else
                        <i class="fa-solid fa-shield-halved"></i> Wazuh
                    @endif
                </span>
            </div>

            <div class="detail-item">
                <span class="detail-label">URL</span>
                <span class="detail-value">{{ $connector->full_url }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Port</span>
                <span class="detail-value">{{ $connector->api_port ?? $connector->effective_port }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Identifiant</span>
                <span class="detail-value">{{ $connector->auth_username ?? 'Aucun' }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Mot de passe</span>
                <span class="detail-value">{{ $connector->has_password ? 'Configuré' : 'Aucun' }}</span>
            </div>

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

            <div class="detail-item">
                <span class="detail-label">Statut actuel</span>
                <span class="detail-value">
                    <span class="status-dot {{ $dotClass }}" style="{{ $dotStyle }}"></span>
                    {{ $statusLabel }}
                </span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Dernière vérification</span>
                <span class="detail-value">{{ $connector->last_check_at ? $connector->last_check_at->diffForHumans() : 'Jamais' }}</span>
            </div>

            @if ($connector->last_error_message)
                <div class="detail-item" style="grid-column: span 2;">
                    <span class="detail-label">Dernière erreur</span>
                    <span class="detail-value" style="color:var(--error-color);">{{ $connector->last_error_message }}</span>
                </div>
            @endif

        </div>
    </div>

</div>


{{-- BOUTON TEST --}}
<div style="margin-top:24px;">
    <button
        type="button"
        class="btn btn-primary"
        id="test-btn"
        onclick="runTest('{{ $connector->id }}', this)"
    >
        <i class="fa-solid fa-plug"></i>
        Lancer le test
    </button>
</div>


{{-- RÉSULTAT --}}
<div id="test-result-card" style="display:none;margin-top:24px;">
    <div class="entity-details" id="test-result-content">
        <!-- Rempli par JS -->
    </div>
</div>


{{-- DERNIERS LOGS --}}
<div style="margin-top:32px;">
    <div class="entity-details">
        <div class="entity-details-header">
            <h2 style="margin:0;font-size:16px;">Derniers tests</h2>
        </div>

        <div style="padding:20px 24px;">
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
</div>


{{-- ACTIONS --}}
<div style="margin-top:24px;">
    <a href="{{ route('connectors.show', $connector) }}" class="btn btn-cancel">Retour au détail</a>
    <a href="{{ route('connectors.index') }}" class="btn btn-cancel" style="margin-left:10px;">Retour à la liste</a>
</div>

</div>
</div>

@endsection