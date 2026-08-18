@extends('layout.app')
@section('agent')
@section('content')

        <div class="page-title">
            <h1>Connecteurs</h1>
            <p>Gérez et surveillez vos agents de collecte déployés sur vos serveurs.</p>
        </div>
        <button class= "usr-btn">
            <i class="fa-solid fa-plus"></i>
                 Ajouter un Agent
        </button>

        {{-- Rangée de KPIs --}}
        <div class="usr-kpi-row">
            <div class="kpi-card">
                <div class="kpi-icon c-teal"><i class="fa-solid fa-robot"></i></div>
                <p class="kpi-label">Agents enregistrés</p><p class="kpi-value">6</p>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon c-sage"><i class="fa-solid fa-circle-check"></i></div>
                <p class="kpi-label">Agents actifs</p><p class="kpi-value">5</p>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon c-red"><i class="fa-solid fa-circle-xmark"></i></div>
                <p class="kpi-label">Agents hors ligne</p><p class="kpi-value">1</p>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon c-orange"><i class="fa-solid fa-database"></i></div>
                <p class="kpi-label">Données 24h</p><p class="kpi-value">12.4 Go</p>
            </div>
        </div>

        {{-- Contenu principal : Tableau + Détails de l'agent --}}
        <div class="agent-layout">
            <div class="panel">
                <div class="panel-header">
                    <div class="search-bar">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" placeholder="Rechercher un agent...">
                    </div>
                    <div class="search-filter">
                        <select>
                            <option>Tous les statuts</option>
                            <option>En ligne</option>
                            <option>Hors ligne</option>
                        </select>
                    </div>
                </div>

                <table class="server-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>IP</th>
                            <th>Statut</th>
                            <th>Dernière activité</th>
                            <th>Données envoyées</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="agent-selected">
                            <td>server01</td>
                            <td>192.168.1.10</td>
                            <td><span class="status-dot online"></span>En ligne</td>
                            <td>Il y a 2 min</td>
                            <td>3.2 Go</td>
                            <td>
                                <button class="icon-btn"><i class="fa-solid fa-eye"></i></button>
                                <button class="icon-btn"><i class="fa-solid fa-pen"></i></button>
                                <button class="icon-btn"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>server02</td>
                            <td>192.168.1.11</td>
                            <td><span class="status-dot online"></span>En ligne</td>
                            <td>Il y a 5 min</td>
                            <td>2.8 Go</td>
                            <td>
                                <button class="icon-btn"><i class="fa-solid fa-eye"></i></button>
                                <button class="icon-btn"><i class="fa-solid fa-pen"></i></button>
                                <button class="icon-btn"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>server03</td>
                            <td>192.168.1.12</td>
                            <td><span class="status-dot online"></span>En ligne</td>
                            <td>Il y a 1 min</td>
                            <td>4.1 Go</td>
                            <td>
                                <button class="icon-btn"><i class="fa-solid fa-eye"></i></button>
                                <button class="icon-btn"><i class="fa-solid fa-pen"></i></button>
                                <button class="icon-btn"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>db-master</td>
                            <td>192.168.1.20</td>
                            <td><span class="status-dot" style="background:var(--text-muted);"></span>Hors ligne</td>
                            <td>Il y a 2h</td>
                            <td>0 Go</td>
                            <td>
                                <button class="icon-btn"><i class="fa-solid fa-eye"></i></button>
                                <button class="icon-btn"><i class="fa-solid fa-pen"></i></button>
                                <button class="icon-btn"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>web-front</td>
                            <td>192.168.1.30</td>
                            <td><span class="status-dot online"></span>En ligne</td>
                            <td>Il y a 10 min</td>
                            <td>1.5 Go</td>
                            <td>
                                <button class="icon-btn"><i class="fa-solid fa-eye"></i></button>
                                <button class="icon-btn"><i class="fa-solid fa-pen"></i></button>
                                <button class="icon-btn"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>cache-redis</td>
                            <td>192.168.1.40</td>
                            <td><span class="status-dot online"></span>En ligne</td>
                            <td>Il y a 3 min</td>
                            <td>0.8 Go</td>
                            <td>
                                <button class="icon-btn"><i class="fa-solid fa-eye"></i></button>
                                <button class="icon-btn"><i class="fa-solid fa-pen"></i></button>
                                <button class="icon-btn"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Panneau de détails de l'agent sélectionné --}}
            <div class="panel agent-detail-panel">
                <div class="agent-detail-header">
                    <div class="kpi-icon c-teal" style="width:40px;height:40px;font-size:18px;"><i class="fa-solid fa-server"></i></div>
                    <div>
                        <p style="margin:0;font-weight:600;font-size:16px;">server01</p>
                        <p style="margin:2px 0 0;font-size:12px;color:var(--text-muted);">192.168.1.10</p>
                    </div>
                    <span class="status-dot online" style="width:10px;height:10px;margin-left:auto;"></span>
                </div>

                <div class="agent-detail-stats">
                    <div class="agent-detail-stat">
                        <p class="agent-detail-stat-label">Statut</p>
                        <p class="agent-detail-stat-value" style="color:var(--sage-green);">En ligne</p>
                    </div>
                    <div class="agent-detail-stat">
                        <p class="agent-detail-stat-label">Données collectées</p>
                        <p class="agent-detail-stat-value">3.2 Go</p>
                    </div>
                    <div class="agent-detail-stat">
                        <p class="agent-detail-stat-label">Dernière activité</p>
                        <p class="agent-detail-stat-value">Il y a 2 min</p>
                    </div>
                    <div class="agent-detail-stat">
                        <p class="agent-detail-stat-label">Version Agent</p>
                        <p class="agent-detail-stat-value">v1.2.4</p>
                    </div>
                </div>

                <div class="panel-header" style="margin-top:10px;">
                    <p>Utilisation CPU (24h)</p>
                </div>
                <div class="alertChart">
                    <canvas id="agentCpuChart"></canvas>
                </div>

                <div class="panel-header" style="margin-top:10px;">
                    <p>Utilisation RAM (24h)</p>
                </div>
                <div class="alertChart">
                    <canvas id="agentRamChart"></canvas>
                </div>
            </div>
        </div>

        <p class="sync-time">Dernière synchronisation : il y a 2 min</p>
@endsection