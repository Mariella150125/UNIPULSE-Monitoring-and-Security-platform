@extends('layout.app')
@section('content')

@if ($errors->any())
        <div class="flash-message error">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif
@if(session('success'))
    <div class="success-message" id="success-message">
        <i class="fa-solid fa-circle-check"></i>
        {{ session('success') }}
    </div>
@endif
    <div class="page-title">
        <h1>Applications Management</h1>
        <p>Manage and monitor your applications</p>
    </div>
    <div class="page-top-action">
        <button
        type="button"
        class="usr-btn"
        data-modal-open="application-modal"
        >
            <i class="fa-solid fa-plus"></i>
            Add Application
        </button>
    </div>
    {{-- ─── KPIs ─── --}}
    <div class="usr-kpi-row">
        <div class="kpi-card">
            <div class="kpi-icon c-teal"><i class="fa-solid fa-table-cells-large"></i></div>
          
            <p class="kpi-label">Total Applications</p>
            <p class="kpi-value"> {{ $applications->total() }}</p>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon c-sage"><i class="fa-solid fa-circle-check"></i></div>

            <p class="kpi-label">Active Applications</p>
            <p class="kpi-value">{{ $activeApplications }}</p>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon c-red"><i class="fa-solid fa-triangle-exclamation"></i></div>
      
            <p class="kpi-label">Critical Issues</p>
            <p class="kpi-value">{{ $criticalIssues }}</p>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon c-orange"><i class="fa-solid fa-cloud"></i></div>
    
            <p class="kpi-label">App en maintenance</p>
            <p class="kpi-value">{{ $maintenance}}</p>
        </div>
    </div>

    {{-- ─── Graphiques ─── --}}
    <div class="grid-2">
        <div class="panel">
            
            <div class="panel-header">
                <p>Application Availability</p>
                <select class="period-btn" id="environmentFilter" onchange="applyEnvFilter()">
                    <option value="" {{ request('environment') == '' ? 'selected' : '' }}>Tous</option>
                    <option value="production" {{ request('environment') == 'production' ? 'selected' : '' }}>Production</option>
                    <option value="staging" {{ request('environment') == 'staging' ? 'selected' : '' }}>Staging</option>
                    <option value="development" {{ request('environment') == 'development' ? 'selected' : '' }}>Development</option>
                    <option value="test" {{ request('environment') == 'test' ? 'selected' : '' }}>Test</option>
                </select>
            </div>
            <div class="alertChart">
                <canvas id="availabilityChart"></canvas>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <p>By Environment</p>
                <button class="period-btn">Tous <i class="fa-solid fa-chevron-down"></i></button>
            </div>
            <div class="donut-wrapper">
                <div class="donut-chart">
                    <canvas id="appEnvDonutChart"></canvas>
                </div>
                <div class="donut-legend" id="appDonutLegend"></div>
            </div>
        </div>
    </div>

   
    {{-- ─── Tableau ─── --}}
    <div class="panel" id="apps-table">
        {{-- RECHERCHE + FILTRES --}}
        <form method="GET" action="{{ route('appli.index') }}" class="search-filter-form">
            <div class="panel-header">
                <div class="search-bar">
                <i class="fa-solid fa-magnifying-glass"></i>

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Rechercher une application..."
                >
            </div>

            <div class="search-filter">

                {{-- ENVIRONNEMENT --}}
                <select name="environment" class="filter-btn">

                    <option value="">
                        Tous les environnements
                    </option>

                    <option
                        value="Production"
                        @selected(request('environment') === 'production')
                    >
                        Production
                    </option>

                    <option
                        value="Préproduction"
                        @selected(request('environment') === 'preproduction')
                    >
                        Préproduction
                    </option>

                    <option
                        value="Development"
                        @selected(request('environment') === 'development')
                    >
                        Development
                    </option>

                    <option
                        value="test"
                        @selected(request('environment') === 'test')
                    >
                        Test
                    </option>

                </select>


                {{-- STATUT --}}
                <select name="status" class="filter-btn">

                    <option value="">
                        Tous les statuts
                    </option>

                    <option
                        value="active"
                        @selected(request('status') === 'active')
                    >
                        Actif
                    </option>

                    <option
                        value="maintenance"
                        @selected(request('status') === 'maintenance')
                    >
                        En maintenance
                    </option>

                    <option value="planned"
                        @selected(request('status') === 'planned')>
                        Planifiée
                    </option>

                    <option value="development"
                        @selected(request('status') === 'development')>
                        En développement
                    </option>

                    <option value="testing"
                        @selected(request('status') === 'testing')>
                        En test
                    </option>

                    <option value="suspended"
                        @selected(request('status') === 'suspended')>
                        Suspendue
                    </option>

                    <option value="retired"
                        @selected(request('status') === 'retired')>
                        Retirée
                    </option>

                    </select>


                <button type="submit" class="filter-btn">
                    <i class="fa-solid fa-filter"></i>
                    Filtrer
                </button>

            </div>

        </form>

    </div>


    {{-- TABLEAU --}}
    <table class="server-table">

        <thead>

            <tr>
                <th>ID</th>
                <th>Application</th>
                <th>Environnement</th>
                <th>Statut</th>
                <th>Version</th>
                <th>Disponibilité</th>
                <th>Dernière vérification</th>
                <th>Actions</th>
            </tr>

        </thead>


        <tbody>

            @forelse ($applications as $app)

                <tr>

                    <td>


                            {{ $app->identifiant_genere }}


                    </td>

                    {{-- APPLICATION --}}
                    <td>

                       <a href="{{ route('monitoring.application.show', $app->id) }}" class="app-name-cell">
                            <span class="app-icon-sm c-teal">
                                <i class="fa-solid fa-globe"></i>
                            </span>
                            {{ $app->name }}
                        </a> 
                    </td>


                    {{-- ENVIRONNEMENT --}}
                    <td>

                        <span class="env-badge">

                            {{ $app->environment }}

                        </span>

                    </td>


                                    {{-- STATUT --}}
                    <td>
                        {{-- Le point coloré (CSS s'occupe de la couleur automatiquement) --}}
                        <span class="status-dot status-{{ $app->status }}"></span>
                        
                        {{-- Le texte (on utilise un petit tableau pour traduire proprement) --}}
                        @php
                            $labels = [
                                'active'      => 'Active',
                                'maintenance' => 'En maintenance',
                                'suspended'   => 'Suspendue',
                                'retired'     => 'Retirée',
                                'planned'     => 'Planifiée',
                                'development' => 'En développement',
                                'testing'     => 'En test',
                                'staging'     => 'Préproduction',
                            ];
                        @endphp
                        {{ $labels[$app->status] ?? ucfirst($app->status) }}
                    </td>   

                    {{-- VERSION --}}
                    <td>
                        {{ $app->version ?? '—' }}
                    </td>


                                    {{-- DISPONIBILITÉ --}}
                    <td>
                        @php
                            // On récupère le pourcentage calculé par le contrôleur, 100% par défaut si pas d'historique
                            $appAvail = isset($availabilities[$app->id]) ? round($availabilities[$app->id]) : 100;
                            // Couleur dynamique (Vert > 95%, Orange > 80%, Rouge sinon)
                            $availColor = $appAvail >= 95 ? 'var(--dark-teal)' : ($appAvail >= 80 ? 'var(--orange)' : 'var(--red)');
                        @endphp
                        <span class="avail-value" style="color: {{ $availColor }}; font-weight: 600;">
                            {{ $appAvail }}%
                        </span>
                    </td>

                    {{-- DERNIÈRE VÉRIFICATION --}}
                    <td>

                        @if($app->last_sync_at)

                            {{ $app->last_sync_at->diffForHumans() }}

                        @else

                            Jamais

                        @endif

                    </td>

                
                    
                                            {{-- MENU D'ACTIONS (3 POINTS) --}}
                        <td>
                            <div class="action-dropdown">
                                <button class="icon-btn action-dropdown-toggle" title="Actions">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </button>
                                <div class="action-dropdown-menu">
                                    <a href="{{ route('appli.show', $app->id) }}" class="dropdown-item">
                                        <i class="fa-solid fa-eye"></i> Voir
                                    </a>
                                    <a href="{{ route('appli.edit', $app->id) }}" class="dropdown-item">
                                        <i class="fa-solid fa-pen"></i> Modifier
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    
                                    {{-- GESTION DES STATUTS --}}
                                    
                                    @if($app->status !== 'active')
                                        <form action="{{ route('appli.status', $app->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="active">
                                            <button type="submit" class="dropdown-item">
                                                <i class="fa-solid fa-circle-play"></i> Activer
                                            </button>
                                        </form>
                                    @endif

                                    @if($app->status !== 'maintenance')
                                        <form action="{{ route('appli.status', $app->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="maintenance">
                                            <button type="submit" class="dropdown-item">
                                                <i class="fa-solid fa-screwdriver-wrench"></i> Mettre en maintenance
                                            </button>
                                        </form>
                                    @endif

                                    @if($app->status !== 'suspended')
                                        <form action="{{ route('appli.status', $app->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="suspended">
                                            <button type="submit" class="dropdown-item">
                                                <i class="fa-solid fa-circle-pause"></i> Suspendre
                                            </button>
                                        </form>
                                    @endif

                                    @if($app->status !== 'retired')
                                        <form action="{{ route('appli.status', $app->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="retired">
                                            <button type="submit" class="dropdown-item text-red">
                                                <i class="fa-solid fa-circle-xmark"></i> Retirer
                                            </button>
                                        </form>
                                    @endif

                                    <div class="dropdown-divider"></div>
                                    <a href="{{ route('appli.delete', $app->id) }}" class="dropdown-item text-red">
                                        <i class="fa-solid fa-trash"></i> Supprimer définitivement
                                    </a>
                                </div>
                            </div>
                        </td>

                </tr>

            @empty

                <tr>

                    <td colspan="7" style="text-align:center;">
                        Aucune application trouvée.
                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>


    {{-- =========================
     PAGINATION
========================= --}}

    <div class="pagination">

        {{-- PRECEDENTE --}}

        @if ($applications->onFirstPage())

            <button
                class="pagination-btn"
                disabled
            >
                <i class="fa-solid fa-chevron-left"></i>
            </button>

        @else

            <a
                href="{{ $applications->previousPageUrl() }}"
                class="pagination-btn"
            >
                <i class="fa-solid fa-chevron-left"></i>
            </a>

        @endif


        {{-- NUMEROS --}}

        @for ($page = 1; $page <= $applications->lastPage(); $page++)

            @if ($page == $applications->currentPage())

                <a
                    href="{{ $applications->url($page) }}"
                    class="pagination-btn active-page"
                >
                    {{ $page }}
                </a>

            @else

                <a
                    href="{{ $applications->url($page) }}"
                    class="pagination-btn"
                >
                    {{ $page }}
                </a>

            @endif

        @endfor


        {{-- SUIVANTE --}}

        @if ($applications->hasMorePages())

            <a
                href="{{ $applications->nextPageUrl() }}"
                class="pagination-btn"
            >
                <i class="fa-solid fa-chevron-right"></i>
            </a>

        @else

            <button
                class="pagination-btn"
                disabled
            >
                <i class="fa-solid fa-chevron-right"></i>
            </button>

        @endif

    </div>


</div>
    <p class="sync-time">
        Dernière synchronisation : {{ $lastSync ? $lastSync->diffForHumans() : 'Jamais' }}
    </p>    
    </div>

    @include('administration.applis.appli-modal')
<script>
    // Pont de données : PHP génère le JSON et le donne au JavaScript
    window.availabilityLabels = @json($availabilityLabels);
    window.availabilityData = @json($availabilityData);
</script>
<script>
    function applyEnvFilter() {
        const env = document.getElementById('environmentFilter').value;
        const url = new URL(window.location.href);
        
        // On met à jour le paramètre "environment" dans l'URL
        if (env) {
            url.searchParams.set('environment', env);
        } else {
            url.searchParams.delete('environment');
        }
        
        // On recharge la page avec la nouvelle URL
        window.location.href = url.toString();
    }
</script>
@endsection