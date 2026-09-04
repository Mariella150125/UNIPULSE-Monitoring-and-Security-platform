
@extends('layout.app')

@section('Webhook')

@endsection

@section('content')
@if(session('success'))
    <div class="success-message">
        <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger" style="background: rgba(192, 57, 43, 0.1); border: 1px solid var(--red); color: var(--red); padding: 12px 18px; border-radius: 8px; margin-bottom: 20px;">
        <i class="fa-solid fa-triangle-exclamation"></i> {{ session('error') }}
    </div>
@endif

<div class="toast-container" id="toastContainer"></div>

<div class="page-title">
    <h1>Gestion API REST & Webhooks</h1>
</div>
<div class="grid-2-api">
    <div class="page-top-action">
        <button class="usr-btn" data-modal-open="webhook-modal">
            <i class="fa-solid fa-plus"></i> Ajouter un Webhook
        </button>
    </div>
    <div class="page-top-action">
        <button class="usr-btn-1" data-modal-open="endpoint-modal">
            <i class="fa-solid fa-plus"></i> Ajouter un Endpoint
        </button>
    </div>
</div>
{{-- Rangée de KPIs --}}
<div class="usr-kpi-row">

    <div class="kpi-card">
        <div class="kpi-icon c-teal">
            <i class="fa-solid fa-code"></i>
        </div>
        <p class="kpi-label">Total API Endpoints</p>
        <p class="kpi-value">{{ $endpointStats['total'] }}</p>
    </div>

    <div class="kpi-card">
        <div class="kpi-icon c-sage">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <p class="kpi-label">API Actives</p>
        <p class="kpi-value">{{ $endpointStats['healthy'] }}</p>
    </div>

    <div class="kpi-card">
        <div class="kpi-icon c-teal">
            <i class="fa-solid fa-satellite-dish"></i>
        </div>
        <p class="kpi-label">Webhooks configurés</p>
        <p class="kpi-value">{{ $webhookStats['total'] }}</p>
    </div>

    <div class="kpi-card">
        <div class="kpi-icon c-red">
            <i class="fa-solid fa-bug"></i>
        </div>
        <p class="kpi-label">Erreurs 24h</p>
        <p class="kpi-value">{{ $totalErrors24h }}</p>
    </div>

</div>

{{-- Tableaux API REST + Webhooks --}}
<div class="grid-2">

    <div class="panel">

        <div class="panel-header">

            <div class="search-bar">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input
                    type="text"
                    placeholder="Rechercher une API..."
                    data-filter-table="endpoints-table"
                >
            </div>

            <div class="search-filter">

                <select class="filter-btn" data-filter-method="endpoints-table">
                    <option value="">Toutes les méthodes</option>
                    <option value="GET">GET</option>
                    <option value="POST">POST</option>
                    <option value="PUT">PUT</option>
                </select>

                <select class="filter-btn" data-filter-status="endpoints-table">
                    <option value="">Tous les statuts</option>
                    <option value="success">Actif</option>
                    <option value="timeout">Warning</option>
                    <option value="never_checked">Inactif</option>
                </select>

            </div>

        </div>

        <table class="server-table" id="endpoints-table">

            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Méthode</th>
                    <th>Statut</th>
                    <th>Dernier appel</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

                @forelse ($endpoints as $ep)

                    <tr
                        data-method="{{ $ep->http_method }}"
                        data-status="{{ $ep->last_status }}"
                    >

                        <td>
                            <span class="font-medium">
                                {{ $ep->application?->name }}
                            </span>
                            <br>
                            <span class="url-cell">
                                {{ $ep->url }}
                            </span>
                        </td>

                        <td>
                            <span class="method-badge method-{{ strtolower($ep->http_method) }}">
                                {{ $ep->http_method }}
                            </span>
                        </td>

                        <td>
                            <span class="status-badge {{ $ep->last_status }}">
                                <span class="status-dot"></span>

                                {{ match($ep->last_status) {
                                    'success'       => 'Actif',
                                    'timeout'       => 'Warning',
                                    'http_4xx'      => 'Erreur 4xx',
                                    'http_5xx'      => 'Erreur 5xx',
                                    'never_checked' => 'Inactif',
                                    default         => $ep->last_status,
                                } }}

                            </span>
                        </td>

                        <td class="text-sm text-muted">
                            {{ $ep->last_checked_at ? $ep->last_checked_at->diffForHumans() : 'Jamais' }}
                        </td>

                        <td>

                            <button
                                class="icon-btn"
                                title="Voir le cURL"
                                data-load-curl="{{ $ep->id }}"
                            >
                                <i class="fa-solid fa-eye"></i>
                            </button>

                            <button
                                class="icon-btn"
                                title="Tester"
                                data-test-endpoint="{{ $ep->id }}"
                            >
                                <i class="fa-solid fa-bolt"></i>
                            </button>

                            <button
                                class="icon-btn"
                                title="Modifier"
                                data-edit-endpoint="{{ $ep->id }}"
                            >
                                <i class="fa-solid fa-pen"></i>
                            </button>

                            <button
                                class="icon-btn"
                                title="Supprimer"
                                data-delete-endpoint="{{ $ep->id }}"
                                data-delete-name="{{ $ep->application?->name }}"
                            >
                                <i class="fa-solid fa-trash icon-danger"></i>
                            </button>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="5" class="table-empty">
                            Aucun endpoint configuré.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>


    <div class="panel">

        <div class="panel-header">

            <div class="search-bar">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input
                    type="text"
                    placeholder="Rechercher un webhook..."
                    data-filter-table="webhooks-table"
                >
            </div>

            <div class="search-filter">

                <select class="filter-btn" data-filter-event="webhooks-table">
                    <option value="">Tous les événements</option>

                    @foreach($webhookEventTypes as $et)
                        <option value="{{ $et->code }}">
                            {{ $et->code }}
                        </option>
                    @endforeach

                </select>

                <select class="filter-btn" data-filter-status="webhooks-table">
                    <option value="">Tous les statuts</option>
                    <option value="active">Actif</option>
                    <option value="paused">En pause</option>
                    <option value="error">En erreur</option>
                </select>

            </div>

        </div>

        <table class="server-table" id="webhooks-table">

            <thead>
                <tr>
                    <th>Nom</th>
                    <th>URL Cible</th>
                    <th>Événement</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

                @forelse ($webhooks as $wh)

                    <tr
                        data-status="{{ $wh->status }}"
                        data-events="{{ $wh->eventTypes->pluck('code')->join(',') }}"
                    >

                        <td class="font-medium">
                            {{ $wh->name }}
                        </td>

                        <td
                            class="url-cell"
                            title="{{ $wh->target_url }}"
                        >
                            {{ $wh->target_url }}
                        </td>

                        <td>
                            <div class="event-badges">

                                @foreach($wh->eventTypes as $et)
                                    <span class="event-badge">
                                        {{ $et->code }}
                                    </span>
                                @endforeach

                            </div>
                        </td>

                        <td>

                            <span class="status-badge {{
                                $wh->status === 'active'
                                    ? 'success'
                                    : ($wh->status === 'paused'
                                        ? 'never_checked'
                                        : 'timeout')
                            }}">

                                <span class="status-dot"></span>

                                {{
                                    $wh->status === 'active'
                                        ? 'Actif'
                                        : ($wh->status === 'paused'
                                            ? 'En pause'
                                            : 'En erreur')
                                }}

                            </span>

                        </td>

                        <td>

                            <button
                                class="icon-btn"
                                title="Modifier"
                                data-edit-webhook="{{ $wh->id }}"
                            >
                                <i class="fa-solid fa-pen"></i>
                            </button>

                            <button
                                class="icon-btn"
                                title="Supprimer"
                                data-delete-webhook="{{ $wh->id }}"
                                data-delete-name="{{ $wh->name }}"
                            >
                                <i class="fa-solid fa-trash icon-danger"></i>
                            </button>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="5" class="table-empty">
                            Aucun webhook configuré.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>


{{-- Section basse --}}
<div class="grid-3">

    <div class="panel">

        <div class="panel-header">
            <p>Exemple de requête</p>

            <button
                class="period-btn"
                id="copyCurl"
                data-copy-curl
            >
                <i class="fa-regular fa-copy"></i> Copier
            </button>
        </div>

        <div class="api-code-block" id="curlBlock">

            <code>
                <span class="api-cmd">Sélectionne un endpoint</span>
                <br>
                <span class="text-muted">
                    Cliquez sur l'icône œil d'un endpoint pour voir l'exemple cURL.
                </span>
            </code>

        </div>

    </div>


    <div class="panel">

        <div class="panel-header">
            <p>Sécurité API</p>
        </div>

        <div class="api-sec-metrics">

            <div class="api-sec-row">

                <div>
                    <p class="api-sec-label">Clés API actives</p>
                    <p class="api-sec-value">
                        {{ $apiStats['active_count'] }}
                    </p>
                </div>

                <div class="kpi-icon c-teal kpi-icon-sm">
                    <i class="fa-solid fa-key"></i>
                </div>

            </div>


            <div class="api-sec-row">

                <div class="api-sec-row-flex">

                    <p class="api-sec-label">
                        Taux d'authentification
                    </p>

                    <p class="api-sec-value">
                        {{ $apiStats['auth_success_rate'] }}%
                    </p>

                    <div class="api-progress">
                        <div
                            class="api-progress-bar"
                            style="width:{{ $apiStats['auth_success_rate'] }}%;"
                        ></div>
                    </div>

                </div>

            </div>


            <div class="api-sec-row">

                <div>
                    <p class="api-sec-label">
                        Requêtes bloquées (24h)
                    </p>

                    <p class="api-sec-value text-red">
                        {{ $apiStats['blocked_requests_24h'] }}
                    </p>
                </div>

                <div class="kpi-icon c-red kpi-icon-sm">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>

            </div>

        </div>

    </div>


    <div class="panel">

        <div class="panel-header">
            <p>Événements classifiés</p>
        </div>

        <div class="api-event-metrics">

            @foreach([
                ['key' => 'critical', 'label' => 'Alertes Critiques', 'color' => 'var(--red)'],
                ['key' => 'major',    'label' => 'Alertes Majeures',  'color' => 'var(--orange)'],
                ['key' => 'minor',    'label' => 'Alertes Mineures',  'color' => 'var(--sage-green)'],
                ['key' => 'info',     'label' => 'Informations',      'color' => 'var(--text-muted)'],
            ] as $row)

                <div class="api-event-row">

                    <span
                        class="api-event-dot"
                        style="background:{{ $row['color'] }};"
                    ></span>

                    <div class="api-event-info">

                        <p class="api-event-name">
                            {{ $row['label'] }}
                        </p>

                        <div class="api-event-bar">

                            <div
                                class="api-event-bar-fill"
                                style="
                                    width:{{ $maxEvents > 0 ? ((int) $eventCounts[$row['key']] / (int) $maxEvents * 100) : 0 }}%;
                                    background:{{ $row['color'] }};
                                "
                            ></div>

                        </div>

                    </div>

                    <span class="api-event-count">
                        {{ $eventCounts[$row['key']] }}
                    </span>

                </div>

            @endforeach

        </div>

    </div>


    <div class="panel grid-span-2">

        <div class="panel-header">

            <p>Clés API</p>

            <button
                class="usr-btn"
                data-modal-open="api-key-modal"
            >
                <i class="fa-solid fa-key"></i> Nouvelle Clé
            </button>

        </div>


        <table class="server-table" id="api-keys-table">

            <thead>

                <tr>
                    <th>Nom</th>
                    <th>Préfixe</th>
                    <th>Créée le</th>
                    <th>Dernière utilisation</th>
                    <th>Expiration</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>

            </thead>

            <tbody>

                @forelse ($apiKeys as $key)

                    <tr>

                        <td class="font-medium">
                            {{ $key->name }}
                        </td>

                        <td>
                            <span class="key-prefix">
                                {{ $key->key_prefix }}…
                            </span>
                        </td>

                        <td class="text-sm">
                            {{ $key->created_at->format('d/m/Y') }}
                        </td>

                        <td class="text-sm text-muted">
                            {{ $key->last_used_at?->diffForHumans() ?? 'Jamais' }}
                        </td>

                        <td class="text-sm">

                            @if($key->expires_at)

                                {{ $key->expires_at->format('d/m/Y') }}

                            @else

                                <span class="text-muted">Jamais</span>

                            @endif

                        </td>

                        <td>

                            <span class="key-status {{
                                $key->is_expired
                                    ? 'expired'
                                    : $key->status
                            }}">

                                <span class="status-dot"></span>

                                {{
                                    $key->is_expired
                                        ? 'Expirée'
                                        : match($key->status) {
                                            'active'    => 'Active',
                                            'suspended' => 'Suspendue',
                                            'revoked'   => 'Révoquée',
                                            default     => $key->status
                                        }
                                }}

                            </span>

                        </td>

                        <td>

                            @if($key->status !== 'revoked')

                                <button
                                    class="icon-btn"
                                    title="{{ $key->status === 'suspended' ? 'Réactiver' : 'Suspendre' }}"
                                    data-toggle-key="{{ $key->id }}"
                                >
                                    <i class="fa-solid fa-{{ $key->status === 'suspended' ? 'play' : 'pause' }}"></i>
                                </button>

                            @endif

                            <button
                                class="icon-btn"
                                title="Régénérer"
                                data-regen-key="{{ $key->id }}"
                                data-regen-name="{{ $key->name }}"
                            >
                                <i class="fa-solid fa-rotate"></i>
                            </button>

                            <button
                                class="icon-btn"
                                title="Révoquer définitivement"
                                data-revoke-key="{{ $key->id }}"
                                data-revoke-name="{{ $key->name }}"
                            >
                                <i class="fa-solid fa-ban icon-danger"></i>
                            </button>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="7" class="table-empty">
                            Aucune clé API. Cliquez sur "Nouvelle Clé" pour en créer une.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

<p class="sync-time">
    Dernière synchronisation : il y a 2 min
</p>

@include('administration.webhook.web-modal')

@endsection
