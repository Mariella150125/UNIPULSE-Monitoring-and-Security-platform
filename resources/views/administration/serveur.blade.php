@extends('layout.app')
@section('Utilisateurs')
@section('content')

        <div class="page-title">
            <h1>Gestion des Serveurs</h1>
        </div>
        <button class= "usr-btn">
            <i class="fa-solid fa-server"></i>
            <i class="fa-solid fa-plus"></i>
                 Add Server
        </button>

        {{-- Rangée de 12 KPI, une seule ligne, défilement horizontal --}}
        <div class="usr-kpi-row">
            <div class="kpi-card">
                <div class="kpi-icon c-teal"><i class="fa-solid fa-server"></i></div>
                <p class="kpi-label">Nombre de Serveurs</p><p class="kpi-value-other">18</p>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon c-sage"><i class="fa-solid fa-database"></i></div>
                <p class="kpi-label">Serveurs en santé</p><p class="kpi-value-other">15</p>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon c-teal"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <p class="kpi-label">Serveurs critiques</p><p class="kpi-value">35</p>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon c-sage"><i class="fa-solid fa-layer-group"></i></div>
                <p class="kpi-label">Applications Hébergées</p><p class="kpi-value-other">30</p>
            </div>
        </div>

        {{-- Graphiques conformes au SRS + derniers événements --}}
        
        <div class="grid-2">
                <div class="panel">
                    <div class="panel-header">
                        <p>Évolution des alertes</p>
                        <button class="period-btn">7 derniers jours <i class="fa-solid fa-chevron-down"></i></button>
                    </div>
                    <div class="alertChart">
                        <canvas id="alertChart"></canvas>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-header">
                        <p>Répartition par environnement</p>
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

            <div class="panel">
                <div class="panel-header">
                    <div class="search-bar">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" placeholder="Rechercher un serveur...">
                    </div>
                    <div class="search-filter">
                        
                            <select class="filter-btn">
                                <option>Tous les statuts</option>
                                <option>En Santé</option>
                                <option>Critique</option>
                                <option>Warning</option>
                            </select>
                            <select class="filter-btn">
                                <option>Tous les OS</option>
                                <option>A</option>
                                <option>Inactif</option>
                            </select>
                            <select class="filter-btn">
                                <label for="Tous les environnements">Tous les environnements</label>
                                    <option>Software Dev</option>
                                    <option>Application Support</option>
                            </select>
                    </div>
                </div>

                <table class="server-table">
                    <thead>
                        <tr>
                            <th>Hostname</th>
                            <th>IP Address</th>
                            <th>Operating system</th>
                            <th>Utilisation CPU</th>
                            <th>Utilisation RAM</th>
                            <th>Utilisation Disk</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Mariella Ngwambe</td>
                            <td>mariella@entreprise.com</td>
                            <td>Développeuse</td>
                            <td>Software Dev</td>
                            <td><span class="status-dot online"></span>Actif</td>
                            <td>Il y a 2 min</td>
                            <td class="grid-6">
                                <button class="icon-btn"><i class="fa-solid fa-pen"></i></button>
                                <button class="icon-btn"><i class="fa-solid fa-ellipsis"></i></button>
                                <button class="icon-btn"><i class="fa-solid fa-eye"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="pagination">
                    <button class="pagination-btn"><i class="fa-solid fa-chevron-left"></i></button>
                    <button class="pagination-btn active-page">1</button>
                    <button class="pagination-btn">2</button>
                    <button class="pagination-btn"><i class="fa-solid fa-chevron-right"></i></button>
                </div>
            </div>

            <p class="sync-time">Dernière synchronisation : il y a 2 min</p>
@endsection