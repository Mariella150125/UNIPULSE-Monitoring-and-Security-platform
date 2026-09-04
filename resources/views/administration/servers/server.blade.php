@extends('layout.app')

@section('content')

    @if ($errors->any())
        <div class="flash-message error">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if (session('success'))
        <div class="success-message" id="success-message">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <div class="page-title">
        <h1>Gestion des Serveurs</h1>
    </div>
    <div class="page-top-action">
        <button type="button" class="usr-btn" data-modal-open="server-modal">
            <i class="fa-solid fa-server"></i>
            <i class="fa-solid fa-plus"></i>
            Add Server
        </button>
    </div>


    {{-- KPIs dynamiques --}}
    <div class="usr-kpi-row">
        <div class="kpi-card">
            <div class="kpi-icon c-teal"><i class="fa-solid fa-server"></i></div>
            <p class="kpi-label">Nombre de Serveurs</p>
            <p class="kpi-value">{{ $totalServers }}</p>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon c-sage"><i class="fa-solid fa-heart-pulse"></i></div>
            <p class="kpi-label">En bonne santé</p>
            <p class="kpi-value">{{ $activeServers }}</p>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon c-red"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <p class="kpi-label">Critiques</p>
            <p class="kpi-value">{{ $criticalServers }}</p>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon c-teal"><i class="fa-solid fa-layer-group"></i></div>
            <p class="kpi-label">Apps hébergées</p>
            <p class="kpi-value">{{ $hostedApps }}</p>
        </div>
    </div>


    {{-- Graphiques --}}
    <div class="grid-2">
        <div class="panel">
            <div class="panel-header">
                <p>Évolution des alertes</p>
            </div>
            <div class="alertChart">
                <canvas id="alertChart"></canvas>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <p>Répartition par environnement</p>
            </div>
            <div class="donut-wrapper">
                <div class="donut-chart">
                    <canvas id="envDonutChart"></canvas>
                </div>
                <div class="donut-legend" id="donutLegend"></div>
            </div>
        </div>
    </div>


        {{-- Tableau avec filtres --}}
        <div class="panel">
            <form method="GET" action="{{ route('server.index') }}">

                <div class="panel-header">
                    <div class="search-bar">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input
                            type="text"
                            name="search"
                            id="server-search"
                            placeholder="Rechercher un serveur..."
                            value="{{ request('search') }}"
                        >
                    </div>
                    <div class="search-filter">
                        <select name="environment" class="filter-btn">
                            <option value="">Tous les environnements</option>
                            <option value="Test"
                                @selected(request('environment') == 'Test')>
                                Test
                            </option>
                            <option value="Production"
                                @selected(request('environment') == 'Production')>
                                production
                            </option>
                        </select>
                        <select name="status" class="filter-btn">
                            <option value="">Tous les statuts</option>
                            <option value="healthy" {{ request('status') === 'healthy' ? 'selected' : '' }}>En bonne santé</option>
                            <option value="critical" {{ request('status') === 'critical' ? 'selected' : '' }}>Critique</option>
                            <option value="warning" {{ request('status') === 'warning' ? 'selected' : '' }}>Warning</option>
                            <option value="unknown" {{ request('status') === 'unknown' ? 'selected' : '' }}>Inconnu</option>
                        </select>
                        <select name="os" class="filter-btn">
                            <option value="">Tous les OS</option>
                            <option value="Linux" {{ request('os') === 'Linux' ? 'selected' : '' }}>Linux</option>
                        </select>
                        <button type="submit" class="filter-btn">
                            <i class="fa-solid fa-filter"></i>
                            Filtrer
                        </button>
                        </div>
                           
                </div>
            </form>

            <table class="server-table">
                <thead>
                    <tr>
                        <th>Hostname</th>
                        <th>IP Address</th>
                        <th>OS</th>
                        <th>Environnement</th>
                        <th>Groupe</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($servers as $server)
                        <tr>
                            <td>{{ $server->hostname }}</td>
                            <td style="font-size:13px;">{{ $server->ip_address }}{{ $server->port ? ':' . $server->port : '' }}</td>
                            <td>
                                {{ $server->os }} {{ $server->os_version ? '(' . $server->os_version . ')' : '' }}
                            </td>
                            <td>
                                @php
                                    $envColor = match($server->environment) {
                                        'production' => 'var(--sage-green)',
                                        'staging'    => 'var(--c-orange)',
                                        'development'=> 'var(--c-teal)',
                                        default      => 'var(--text-muted)',
                                    };
                                @endphp
                                <span style="color:{{ $envColor }};font-size:13px;font-weight:500;">
                                    {{ $server->environment }}
                                </span>
                            </td>
                            <td>{{ $server->group?->name ?? '—' }}</td>
                            <td>
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
                                <span class="status-dot {{ $dotClass }}" style="{{ $dotStyle }}"></span>
                                {{ $statusLabel }}
                            </td>
                            <td>
                                <a href="{{ route('server.show', $server) }}" class="icon-btn" title="Voir">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="{{ route('server.edit', $server) }}" class="icon-btn" title="Modifier">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <a href="{{ route('servers.delete', $server->id) }}" class="icon-btn" title="Supprimer" style="color:var(--c-red);">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center;padding:40px;color:var(--text-muted);">
                                Aucun serveur trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $servers->withQueryString()->links() }}
        </div>

        <p class="sync-time">Dernière synchronisation : il y a 2 min</p>

        @include('administration.servers.server-modal')

@endsection


