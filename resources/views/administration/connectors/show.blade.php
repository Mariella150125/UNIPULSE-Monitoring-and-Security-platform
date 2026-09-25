@extends('layout.app')

@section('content')

<div class="main-content">
<div class="dashboard-content">

<div class="page-title">
    <h1>Détails du connecteur</h1>
    <p>{{ $connector->name }}</p>
</div>

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
        <div style="display:flex;gap:10px;">
            <a href="{{ route('connectors.index') }}" class="btn btn-cancel">
                <i class="fa-solid fa-arrow-left"></i>
                Retour
            </a>
            
        </div>
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
                <span class="detail-value">{{ $connector->api_port ?? 'Déduit de l\'URL (' . $connector->effective_port . ')' }}</span>
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
                <span class="detail-label">Statut</span>
                <span class="detail-value">
                    <span class="status-dot {{ $dotClass }}" style="{{ $dotStyle }}"></span>
                    {{ $statusLabel }}

                    @if ($connector->is_prolonged_failure)
                        <i class="fa-solid fa-triangle-exclamation" style="color:var(--error-color);font-size:12px;margin-left:6px;" title="Échec prolongé"></i>
                    @endif
                </span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Dernière vérification</span>
                <span class="detail-value">{{ $connector->last_check_at ? $connector->last_check_at->diffForHumans() : 'Jamais' }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Dernier succès</span>
                <span class="detail-value">{{ $connector->last_success_at ? $connector->last_success_at->diffForHumans() : 'Jamais' }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Configuré par</span>
                <span class="detail-value">{{ $connector->createdBy->name ?? '—' }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Créé le</span>
                <span class="detail-value">{{ $connector->created_at->format('d/m/Y H:i') }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Modifié le</span>
                <span class="detail-value">{{ $connector->updated_at->format('d/m/Y H:i') }}</span>
            </div>

            @if ($connector->last_error_message)
                <div class="detail-item" style="grid-column: span 2;">
                    <span class="detail-label">Dernière erreur</span>
                    <span class="detail-value" style="color:var(--error-color);">{{ $connector->last_error_message }}</span>
                </div>
            @endif

            @if ($connector->extra_config)
                <div class="detail-item" style="grid-column: span 2;">
                    <span class="detail-label">Configuration avancée</span>
                    <pre style="background:var(--page-bg);padding:10px;border-radius:8px;font-size:13px;overflow-x:auto;margin:0;">{{ json_encode($connector->extra_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            @endif

        </div>
    </div>

</div>



</div>
</div>

@endsection