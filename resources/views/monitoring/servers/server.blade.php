@extends('layout.app')

@section('content')

<div class="page-title">
    <h1>Monitoring des Serveurs</h1>
    <p>Vue d'ensemble en temps réel de l'infrastructure </p>
</div>

{{-- LIGNE 1 : Stat Panels (KPIs Globaux) --}}
<div class="usr-kpi-row">
    <div class="kpi-card">
        <div class="kpi-icon c-teal"><i class="fa-solid fa-server"></i></div>
        <p class="kpi-label">Total Serveurs</p>
        <p class="kpi-value">{{ $totalServers }}</p>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon c-sage"><i class="fa-solid fa-circle-check"></i></div>
        <p class="kpi-label">En ligne</p>
        <p class="kpi-value">{{ $healthyServers }}</p>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon c-red"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <p class="kpi-label">Hors ligne / Critique</p>
        <p class="kpi-value">{{ $criticalServers }}</p>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon c-orange"><i class="fa-solid fa-screwdriver-wrench"></i></div>
        <p class="kpi-label">En maintenance</p>
        <p class="kpi-value">{{ $maintenanceServers }}</p>
    </div>
</div>

{{-- LIGNE 2 : Cartes individuelles des serveurs --}}
<div class="grafana-grid">
    @forelse($servers as $server)
        <div class="grafana-panel" data-server-id="{{ $server->id }}">
            <div class="grafana-panel-header">
                <h3>{{ $server->name }}</h3>
                <span class="grafana-status" id="status-{{ $server->id }}">
                    <span class="status-dot unknown"></span> Chargement...
                </span>
            </div>

            <div class="grafana-metrics">
                <!-- CPU -->
                <div class="grafana-metric">
                    <div class="metric-header">
                        <span>CPU Utilisation</span>
                        <strong id="cpu-val-{{ $server->id }}">-- %</strong>
                    </div>
                    <div class="metric-bar-container">
                        <div class="metric-bar" id="cpu-bar-{{ $server->id }}" style="width: 0%"></div>
                    </div>
                </div>

                <!-- RAM -->
                <div class="grafana-metric">
                    <div class="metric-header">
                        <span>Memory Utilisation</span>
                        <strong id="ram-val-{{ $server->id }}">-- %</strong>
                    </div>
                    <div class="metric-bar-container">
                        <div class="metric-bar" id="ram-bar-{{ $server->id }}" style="width: 0%"></div>
                    </div>
                </div>

                <!-- DISK -->
                <div class="grafana-metric">
                    <div class="metric-header">
                        <span>Disk Usage</span>
                        <strong id="disk-val-{{ $server->id }}">-- %</strong>
                    </div>
                    <div class="metric-bar-container">
                        <div class="metric-bar" id="disk-bar-{{ $server->id }}" style="width: 0%; background: var(--orange);"></div>
                    </div>
                </div>
            </div>
            
            <a href="{{ route('monitoring.servers.show', $server->id) }}" class="grafana-detail-link">
                Voir les détails <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    @empty
        <div class="panel" style="grid-column: 1 / -1; text-align: center; padding: 40px;">
            Aucun serveur enregistré. Allez dans Administration > Serveurs pour en ajouter.
        </div>
    @endforelse
</div>

@endsection