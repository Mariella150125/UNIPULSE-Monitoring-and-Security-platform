@extends('layout.app')

@section('content')

<div class="page-title">
    <h1>Monitoring des Serveurs</h1>
    <p>Vue d'ensemble en temps réel (Style Grafana)</p>
</div>

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