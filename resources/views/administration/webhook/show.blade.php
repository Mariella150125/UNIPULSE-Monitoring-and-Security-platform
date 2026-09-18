@extends('layout.app')

@section('content')

<div class="page-title">
    <h1>Détails du Webhook</h1>
</div>

<div class="entity-details">

    <div class="entity-details-header">
        <div>
            <h2>{{ $webhook->name }}</h2>
            <p>{{ $webhook->direction === 'inbound' ? 'Webhook Entrant (Inbound)' : 'Webhook Sortant (Outbound)' }}</p>
        </div>
        <div style="display:flex;gap:10px;">
            <a href="{{ route('webhooks.page') }}" class="btn btn-cancel">
                <i class="fa-solid fa-arrow-left"></i>
                Retour
            </a>
        </div>
    </div>

    <div class="entity-details-body">
        <div class="details-grid">

            <div class="detail-item">
                <span class="detail-label">Statut</span>
                <span class="detail-value">
                    <span class="status-dot {{ $webhook->status === 'active' ? 'online' : 'offline' }}"></span>
                    {{ ucfirst($webhook->status) }}
                </span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Scope</span>
                <span class="detail-value">{{ ucfirst($webhook->scope) }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">URL Cible</span>
                <span class="detail-value">{{ $webhook->target_url ?? '—' }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Méthode d'authentification</span>
                <span class="detail-value">{{ ucfirst(str_replace('_', ' ', $webhook->auth_method)) }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Sévérité minimale</span>
                <span class="detail-value">{{ $webhook->min_severity_level }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Créé par</span>
                <span class="detail-value">{{ $webhook->creator?->name ?? '—' }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Créé le</span>
                <span class="detail-value">{{ $webhook->created_at?->format('d/m/Y à H:i')?? '—'}}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Modifié le</span>
                <span class="detail-value">{{ $webhook->updated_at?->format('d/m/Y à H:i')?? '—'}}</span>
            </div>

            @if ($webhook->eventTypes->isNotEmpty())
                <div class="detail-item" style="grid-column: span 2;">
                    <span class="detail-label">Événements écoutés</span>
                    <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:4px;">
                        @foreach ($webhook->eventTypes as $et)
                            <span class="env-badge">{{ $et->code }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>

</div>

{{-- STATISTIQUES DE LIVRAISON --}}

    <div class="entity-details">
        <div class="entity-details-header">
            <h2 style="margin:0;font-size:16px;">Statistiques de livraison</h2>
        </div>
        <div class="entity-details-body">
            <div class="details-grid">
                <div class="detail-item">
                    <span class="detail-label">Total livraisons</span>
                    <span class="detail-value">{{ $totalDeliveries }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Succès</span>
                    <span class="detail-value" style="color: var(--sage-green);">{{ $successCount }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Échecs</span>
                    <span class="detail-value" style="color: var(--red);">{{ $failureCount }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Taux de réussite</span>
                    <span class="detail-value">{{ $successRate ?? 0 }} %</span>
                </div>
            </div>
        </div>
    </div>


{{-- HISTORIQUE DES LIVRAISONS --}}
@if ($recentDeliveries->isNotEmpty())
    <div style="margin-top:32px;">
        <div class="entity-details">
            <div class="entity-details-header">
                <h2 style="margin:0;font-size:16px;">Dernières livraisons reçues</h2>
            </div>
            <table class="server-table">
                <thead>
                    <tr>
                        <th>Événement</th>
                        <th>Statut</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recentDeliveries as $delivery)
                        <tr>
                            <td>{{ $delivery->eventType->code ?? 'N/A' }}</td>
                            <td>
                                @if($delivery->success)
                                    <span class="status-dot online"></span> Succès
                                @else
                                    <span class="status-dot offline"></span> Échec
                                @endif
                            </td>
                            <td>{{ $delivery->delivered_at->format('d/m/Y à H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@endsection