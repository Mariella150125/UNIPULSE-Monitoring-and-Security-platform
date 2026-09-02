@extends('layout.app')

@section('agent')

@section('content')

    @if ($errors->any())
        <div class="flash-message error">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if (session('success'))
        <div class="flash-message success">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <div class="page-title">
        <h1>Connecteurs</h1>
        <p>Gérez et surveillez les connexions avec vos plateformes de monitoring et de sécurité.</p>
    </div>

    <div class="page-top-action">
        <button type="button" class="usr-btn" data-modal-open="connector-modal">
            <i class="fa-solid fa-plus"></i>
            Ajouter un connecteur
        </button>
    </div>

    <div class="usr-kpi-row">

        <div class="kpi-card">
            <div class="kpi-icon c-teal"><i class="fa-solid fa-plug"></i></div>
            <p class="kpi-label">Connecteurs enregistrés</p>
            <p class="kpi-value">{{ $kpis['total'] }}</p>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon c-sage"><i class="fa-solid fa-circle-check"></i></div>
            <p class="kpi-label">Connectés</p>
            <p class="kpi-value">{{ $kpis['connected'] }}</p>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon c-red"><i class="fa-solid fa-circle-xmark"></i></div>
            <p class="kpi-label">En erreur</p>
            <p class="kpi-value">{{ $kpis['error'] }}</p>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon c-orange"><i class="fa-solid fa-clock"></i></div>
            <p class="kpi-label">Jamais testés</p>
            <p class="kpi-value">{{ $kpis['never'] }}</p>
        </div>

    </div>

    <div class="panel">

        <div class="panel-header">

            <div class="search-bar">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="connector-search" placeholder="Rechercher un connecteur..." value="{{ request('search') }}">
            </div>

            <div class="filter-btn">
                <select id="filter-type"  class="filter-btn">>
                    <option value="">Tous les types</option>
                    <option value="prometheus" {{ request('type') === 'prometheus' ? 'selected' : '' }}>Prometheus</option>
                    <option value="wazuh" {{ request('type') === 'wazuh' ? 'selected' : '' }}>Wazuh</option>
                </select>
                <select id="filter-status"  class="filter-btn">>
                    <option value="">Tous les statuts</option>
                    <option value="connected" {{ request('status') === 'connected' ? 'selected' : '' }}>Connecté</option>
                    <option value="error" {{ request('status') === 'error' ? 'selected' : '' }}>Erreur</option>
                    <option value="never_tested" {{ request('status') === 'never_tested' ? 'selected' : '' }}>Jamais testé</option>
                </select>

                <button type="submit" class="filter-btn">
                    <i class="fa-solid fa-filter"></i>
                    Filtrer
                </button>
            </div>
                
        </div>

        <table class="server-table">

            <thead>
                <tr>
                    <th>Type</th>
                    <th>Nom</th>
                    <th>URL</th>
                    <th>Statut</th>
                    <th>Dernière vérification</th>
                    <th>Configuré par</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

                @forelse ($connectors as $connector)

                    <tr>

                        <td>
                            <span class="connector-type">
                                @if ($connector->type === 'prometheus')
                                    <i class="fa-solid fa-chart-line"></i> Prometheus
                                @else
                                    <i class="fa-solid fa-shield-halved"></i> Wazuh
                                @endif
                            </span>
                        </td>

                        <td>{{ $connector->name }}</td>

                        <td style="font-size:13px;">{{ $connector->full_url }}</td>

                        <td>
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
                                <i class="fa-solid fa-triangle-exclamation" style="color:var(--c-red);font-size:11px;margin-left:4px;" title="Échec prolongé"></i>
                            @endif
                        </td>

                        <td>{{ $connector->last_check_at ? $connector->last_check_at->diffForHumans() : 'Jamais' }}</td>

                        <td>{{ $connector->createdBy->name ?? '—' }}</td>

                        <td>
                            <a href="{{ route('connectors.show', $connector) }}" class="icon-btn" title="Voir">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="{{ route('connectors.edit', $connector) }}" class="icon-btn" title="Modifier">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <a href="{{ route('connectors.plug', $connector) }}" class="icon-btn" title="Tester la connexion">
                                <i class="fa-solid fa-plug"></i>
                            </a>
                            <a href="{{ route('connectors.delete', $connector) }}" class="icon-btn" title="Supprimer" style="color:var(--c-red);">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted);">
                            Aucun connecteur configuré.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

        {{ $connectors->withQueryString()->links() }}

    </div>

    @include('administration.connectors.connect-modal')

@endsection


