@extends('layout.app')
@section('content')

    <div class="page-title">
        <h1>Applications Management</h1>
        <p>Manage and monitor your applications</p>
    </div>
    <button
    type="button"
    class="usr-btn"
    data-modal-open="application-modal"
    >
        <i class="fa-solid fa-plus"></i>
        Add Application
    </button>

    {{-- ─── KPIs ─── --}}
    <div class="usr-kpi-row">
        <div class="kpi-card">
            <div class="kpi-icon c-teal"><i class="fa-solid fa-table-cells-large"></i></div>
            <span class="kpi-change positive"><i class="fa-solid fa-arrow-up"></i> 2.5%</span>
            <p class="kpi-label">Total Applications</p>
            <p class="kpi-value"> {{ $applications->count() }}</p>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon c-sage"><i class="fa-solid fa-circle-check"></i></div>
            <span class="kpi-change positive"><i class="fa-solid fa-arrow-up"></i> 1.8%</span>
            <p class="kpi-label">Active Applications</p>
            <p class="kpi-value">{{ $activeApplications->count() }}</p>
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
                <button class="period-btn">7 derniers jours <i class="fa-solid fa-chevron-down"></i></button>
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
                    <canvas id="envDonutChart"></canvas>
                </div>
                <div class="donut-legend" id="donutLegend"></div>
            </div>
        </div>
    </div>

    {{-- ─── Tableau ─── --}}
    <div class="panel">
        <div class="panel-header">
            <div class="search-bar">
                <form method="GET" action="{{ route('applications.index') }}">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" placeholder="Rechercher une application...">
            </div>
            <div class="search-filter">
                <select name="type" class="filter-btn">
                    <option value="">Tous les environnements</option>
                    <option>Production</option>
                    <option>Staging</option>
                    <option>Development</option>
                    <option>QA</option>
                </select>
                <select name="type" class="filter-btn">
                    <option value="">Tous les statuts</option>
                    <option value="actif" @selected(request('status') == 'actif')">Actif</option>
                    <option value="en maintenance" @selected(request('status') == 'en maintenance')>En maintenance</option>
                </select>
            </div>
        </div>

        <table class="server-table">
            <thead>
                <tr>
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
                        <td>{{ $app->name }}</td>
                        <td>{{ $app->type }}</td>
                        <td>{{ $app->environment }}</td>
                        <td>{{ $app->department }}</td>
                        <td>
                            @if($app->status === 'actif')
                                <span class="status-dot online"></span> Actif
                            @else
                                <span class="status-dot offline"></span> Maintenance
                            @endif
                    <td class="app-name-cell">
                        <span class="app-icon-sm c-teal"><i class="fa-solid fa-globe"></i></span>
                        Company Website
                    </td>
                    <td><span class="env-badge env-prod">Production</span></td>
                    <td><span class="status-dot online"></span>Actif</td>
                    <td>v2.4.1</td>
                    <td><span class="avail-value">99.9%</span></td>
                    <td>Il y a 2 min</td>
                    <td>
                        <button class="icon-btn"><i class="fa-solid fa-eye"></i></button>
                        <button class="icon-btn"><i class="fa-solid fa-pen"></i></button>
                        <button class="icon-btn"><i class="fa-solid fa-trash"></i></button>
                    </td>
                </tr>
                <tr>
                    <td class="app-name-cell">
                        <span class="app-icon-sm c-sage"><i class="fa-solid fa-code"></i></span>
                        REST API
                    </td>
                    <td><span class="env-badge env-prod">Production</span></td>
                    <td><span class="status-dot online"></span>Actif</td>
                    <td>v3.1.0</td>
                    <td><span class="avail-value">99.7%</span></td>
                    <td>Il y a 5 min</td>
                    <td>
                        <button class="icon-btn"><i class="fa-solid fa-eye"></i></button>
                        <button class="icon-btn"><i class="fa-solid fa-pen"></i></button>
                        <button class="icon-btn"><i class="fa-solid fa-trash"></i></button>
                    </td>
                </tr>
                <tr>
                    <td class="app-name-cell">
                        <span class="app-icon-sm c-orange"><i class="fa-solid fa-gauge-high"></i></span>
                        Admin Panel
                    </td>
                    <td><span class="env-badge env-staging">Staging</span></td>
                    <td><span class="status-dot online"></span>Actif</td>
                    <td>v1.8.2</td>
                    <td><span class="avail-value">98.5%</span></td>
                    <td>Il y a 10 min</td>
                    <td>
                        <button class="icon-btn"><i class="fa-solid fa-eye"></i></button>
                        <button class="icon-btn"><i class="fa-solid fa-pen"></i></button>
                        <button class="icon-btn"><i class="fa-solid fa-trash"></i></button>
                    </td>
                </tr>
                <tr>
                    <td class="app-name-cell">
                        <span class="app-icon-sm c-red"><i class="fa-solid fa-mobile-screen"></i></span>
                        Mobile App Backend
                    </td>
                    <td><span class="env-badge env-prod">Production</span></td>
                    <td><span class="status-dot warning"></span>Warning</td>
                    <td>v2.0.0</td>
                    <td><span class="avail-value avail-warning">95.2%</span></td>
                    <td>Il y a 15 min</td>
                    <td>
                        <button class="icon-btn"><i class="fa-solid fa-eye"></i></button>
                        <button class="icon-btn"><i class="fa-solid fa-pen"></i></button>
                        <button class="icon-btn"><i class="fa-solid fa-trash"></i></button>
                    </td>
                </tr>
                <tr>
                    <td class="app-name-cell">
                        <span class="app-icon-sm c-teal"><i class="fa-solid fa-credit-card"></i></span>
                        Payment Gateway
                    </td>
                    <td><span class="env-badge env-prod">Production</span></td>
                    <td><span class="status-dot online"></span>Actif</td>
                    <td>v4.2.1</td>
                    <td><span class="avail-value">99.8%</span></td>
                    <td>Il y a 3 min</td>
                    <td>
                        <button class="icon-btn"><i class="fa-solid fa-eye"></i></button>
                        <button class="icon-btn"><i class="fa-solid fa-pen"></i></button>
                        <button class="icon-btn"><i class="fa-solid fa-trash"></i></button>
                    </td>
                </tr>
                <tr>
                    <td class="app-name-cell">
                        <span class="app-icon-sm c-muted"><i class="fa-solid fa-chart-line"></i></span>
                        Analytics Dashboard
                    </td>
                    <td><span class="env-badge env-dev">Development</span></td>
                    <td><span class="status-dot" style="background:var(--text-muted);"></span>Inactif</td>
                    <td>v0.9.1</td>
                    <td><span class="avail-value avail-na">N/A</span></td>
                    <td>Il y a 2h</td>
                    <td>
                        <button class="icon-btn"><i class="fa-solid fa-eye"></i></button>
                        <button class="icon-btn"><i class="fa-solid fa-pen"></i></button>
                        <button class="icon-btn"><i class="fa-solid fa-trash"></i></button>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="table-footer">
            <p class="sync-time">Dernière synchronisation : il y a 2 min</p>
            <div class="pagination">
                <button class="pagination-btn"><i class="fa-solid fa-chevron-left"></i></button>
                <button class="pagination-btn active-page">1</button>
                <button class="pagination-btn">2</button>
                <button class="pagination-btn"><i class="fa-solid fa-chevron-right"></i></button>
            </div>
        </div>
    </div>
    @include('administration.applis.appli-modal')

@endsection