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
            <span class="kpi-change positive"><i class="fa-solid fa-arrow-up"></i> 2.5%</span>
            <p class="kpi-label">Total Applications</p>
            <p class="kpi-value"> {{ $applications->total() }}</p>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon c-sage"><i class="fa-solid fa-circle-check"></i></div>
            <span class="kpi-change positive"><i class="fa-solid fa-arrow-up"></i> 1.8%</span>
            <p class="kpi-label">Active Applications</p>
            <p class="kpi-value">{{ $activeApplications }}</p>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon c-red"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <span class="kpi-change positive"><i class="fa-solid fa-arrow-down"></i> 0.5%</span>
            <p class="kpi-label">Critical Issues</p>
            <p class="kpi-value">--</p>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon c-orange"><i class="fa-solid fa-cloud"></i></div>
            <span class="kpi-change positive"><i class="fa-solid fa-arrow-up"></i> 0.2%</span>
            <p class="kpi-label">App en maintenance</p>
            <p class="kpi-value">--</p>
        </div>
    </div>

    {{-- ─── Graphiques ─── --}}
    <div class="grid-2">
        <div class="panel">
            
            <div class="panel-header">
                <p>Application Availability</p>
                <select class="period-btn" id="environmentFilter">
                    <option value="">Tous</option>
                    <option value="production">Production</option>
                    <option value="staging">Staging</option>
                    <option value="development">Development</option>
                    <option value="test">Test</option>
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
    {{-- ─── Tableau ─── --}}
    <div class="panel">
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
                        value="Staging"
                        @selected(request('environment') === 'staging')
                    >
                        Staging
                    </option>

                    <option
                        value="Development"
                        @selected(request('environment') === 'development')
                    >
                        Development
                    </option>

                    <option
                        value="QA"
                        @selected(request('environment') === 'test')
                    >
                        QA
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
                    <td class="app-name-cell">

                        <span class="app-icon-sm c-teal">
                            <i class="fa-solid fa-globe"></i>
                        </span>

                        {{ $app->name }}

                    </td>


                    {{-- ENVIRONNEMENT --}}
                    <td>

                        <span class="env-badge">

                            {{ $app->environment }}

                        </span>

                    </td>


                    {{-- STATUT --}}
                    <td>

                        @if($app->status === 'active')

                            <span class="status-dot online"></span>
                            Actif

                        @elseif($app->status === 'maintenance')

                            <span class="status-dot warning"></span>
                            En maintenance

                        @else

                            <span class="status-dot offline"></span>
                            {{ ucfirst($app->status) }}

                        @endif

                    </td>


                    {{-- VERSION --}}
                    <td>
                        {{ $app->version ?? '—' }}
                    </td>


                    {{-- DISPONIBILITÉ --}}
                    <td>

                        {{-- Pour l'instant, cette donnée viendra de Prometheus --}}
                        <span class="avail-value">
                            —
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


                    {{-- ACTIONS --}}
                    <td>

                        {{-- SHOW --}}
                        <a
                            href="{{ route('appli.show', $app->id) }}"
                            class="icon-btn"
                            title="Voir"
                        >
                            <i class="fa-solid fa-eye"></i>
                        </a>


                        {{-- EDIT --}}
                        <a
                            href="{{ route('appli.edit', $app->id) }}"
                            class="icon-btn"
                            title="Modifier"
                        >
                            <i class="fa-solid fa-pen"></i>
                        </a>


                        {{-- DELETE --}}
                        <form
                            action="{{ route('appli.destroy', $app->id) }}"
                            method="POST"
                            style="display:inline;"
                        >

                            @csrf
                            @method('DELETE')

                            <a href="{{ route('appli.delete', $app->id) }}" class="icon-btn" title="Supprimer">
                                <i class="fa-solid fa-trash"></i>
                            </button>

                        </form>

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
        
    </div>

    @include('administration.applis.appli-modal')
{{-- à enlever quand il y auara les vraies données--}}

<script>
document.addEventListener('DOMContentLoaded', function () {

    const availabilityCanvas =
        document.getElementById('availabilityChart');

    if (availabilityCanvas && typeof Chart !== 'undefined') {

        new Chart(availabilityCanvas, {

            type: 'line',

            data: {
                labels: [
                    '04 Août',
                    '05 Août',
                    '06 Août',
                    '07 Août',
                    '08 Août',
                    '09 Août',
                    '10 Août'
                ],

                datasets: [

                    {
                        label: 'CRM',

                        data: [
                            99.2,
                            99.5,
                            98.9,
                            99.7,
                            99.4,
                            99.8,
                            99.6
                        ],

                        borderColor: '#56825E',
                        backgroundColor: 'transparent',

                        tension: 0.35,
                        pointRadius: 3,
                        fill: false
                    },

                    {
                        label: 'Application Mobile',

                        data: [
                            98.4,
                            98.9,
                            98.2,
                            99.1,
                            98.7,
                            99.3,
                            98.8
                        ],

                        borderColor: '#1d4a40',
                        backgroundColor: 'transparent',

                        tension: 0.35,
                        pointRadius: 3,
                        fill: false
                    },

                    {
                        label: 'API Client',

                        data: [
                            99.7,
                            99.6,
                            99.8,
                            99.9,
                            99.5,
                            99.8,
                            99.9
                        ],

                        borderColor: '#8fae94',
                        backgroundColor: 'transparent',

                        tension: 0.35,
                        pointRadius: 3,
                        fill: false
                    },

                    {
                        label: 'Portail Web',

                        data: [
                            97.8,
                            98.1,
                            97.5,
                            98.6,
                            98.2,
                            98.9,
                            98.5
                        ],

                        borderColor: '#e08e3e',
                        backgroundColor: 'transparent',

                        tension: 0.35,
                        pointRadius: 3,
                        fill: false
                    }
                ]
            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                interaction: {
                    mode: 'index',
                    intersect: false
                },

                plugins: {

                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'line',
                            padding: 20,
                            font: {
                                size: 13
                            }
                        }
                    },

                    tooltip: {
                        callbacks: {
                            label: function(context) {

                                return context.dataset.label +
                                    ' : ' +
                                    context.parsed.y +
                                    '%';
                            }
                        }
                    }
                },

                scales: {

                    y: {
                        min: 95,
                        max: 100,

                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    },

                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
});
</script>
@endsection