@extends('layout.app')

@section('content')

<div class="main-content">
<div class="dashboard-content">

<div class="page-title">
    <h1>Détails de l'application</h1>
    <p>{{ $application->identifiant_genere }}</p>
</div>

<div class="entity-details">
    <div class="entity-details-header">
        <div>
            <h2>{{ $application->name }}</h2>
            <p>
                <span class="env-badge env-{{ $application->environment === 'production' ? 'prod' : ($application->environment === 'staging' ? 'staging' : ($application->environment === 'development' ? 'dev' : 'qa')) }}">
                    {{ $application->environment }}
                </span>
                @if($application->status === 'active')
                    <span class="status-dot online" style="margin-left:8px;"></span> Active
                @elseif($application->status === 'maintenance')
                    <span class="status-dot warning" style="margin-left:8px;"></span> Maintenance
                @else
                    <span class="status-dot offline" style="margin-left:8px;"></span> {{ ucfirst($application->status) }}
                @endif
            </p>
        </div>
        <a href="{{ route('appli.index') }}" class="btn btn-cancel">
            <i class="fa-solid fa-arrow-left"></i>
            Retour
        </a>
    </div>

    <div class="entity-details-body">
        <div class="details-grid">

            <div class="detail-item">
                <span class="detail-label">Identifiant</span>
                <span class="detail-value" style="font-family:'Courier New',monospace;">{{ $application->identifiant_genere }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Type</span>
                <span class="detail-value">{{ $application->applicationType?->name ?? '—' }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Environnement</span>
                <span class="detail-value">{{ $application->environment }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Hébergement</span>
                <span class="detail-value">{{ $application->is_hosted ? 'Hébergé' : 'Non hébergé' }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">URL</span>
                <span class="detail-value">{{ $application->url ?? '—' }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Langage</span>
                <span class="detail-value">{{ $application->language ?? '—' }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Framework</span>
                <span class="detail-value">{{ $application->framework ?? '—' }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Version</span>
                <span class="detail-value">{{ $application->version ?? '—' }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Base de données</span>
                <span class="detail-value">{{ $application->database_used ?? '—' }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Criticité</span>
                <span class="detail-value">
                    @match($application->criticality)
                        'low' => 'Basse',
                        'medium' => 'Moyenne',
                        'high' => 'Haute',
                        'critical' => 'Critique',
                        default => 'Non définie',
                    @endmatch
                </span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Serveur</span>
                <span class="detail-value">{{ $application->server?->name ?? '—' }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Port</span>
                <span class="detail-value">{{ $application->port ?? '—' }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">identifiant</span>
                <span class="detail-value">{{ $application->identifiant_genere ?? '—' }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Chemin de déploiement</span>
                <span class="detail-value" style="word-break:break-all;">{{ $application->deployment_path ?? '—' }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Monitoring</span>
                <span class="detail-value">{{ $application->monitoring_enabled ? 'Activé' : 'Désactivé' }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Wazuh</span>
                <span class="detail-value">{{ $application->wazuh_enabled ? 'Activé' : 'Désactivé' }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Client</span>
                <span class="detail-value">{{ $application->client_name ?? '—' }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Responsable</span>
                <span class="detail-value">{{ $application->responsibleUser?->name ?? '—' }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Description</span>
                <span class="detail-value">{{ $application->description ?? '—' }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Créée le</span>
                <span class="detail-value">{{ $application->created_at->format('d/m/Y H:i') }}</span>
            </div>

        </div>
    </div>
</div>

</div>
</div>

@endsection