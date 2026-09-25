@extends('layout.app')

@section('content')

<div class="page-title">
    <h1>Dashboard Global Monitoring</h1>
    <p>Vue d'ensemble en temps réel de l'infrastructure et de la sécurité.</p>
</div>

{{-- LIGNE 1 : Les scores globaux (MF-33) --}}
<div class="usr-kpi-row">
    
    {{-- Health Score Global --}}
    <div class="kpi-card">
        <div class="kpi-icon c-teal"><i class="fa-solid fa-heart-pulse"></i></div>
        <p class="kpi-label">Health Score Global</p>
        <p class="kpi-value">{{ $globalHealthScore }}%</p>
        <div class="kpi-change {{ $globalHealthScore > 80 ? 'positive' : 'negative' }}">
            <i class="fa-solid fa-arrow-{{ $globalHealthScore > 80 ? 'up' : 'down' }}"></i>
            {{ $globalHealthScore > 80 ? 'Sain' : 'Risque' }}
        </div>
    </div>

    {{-- Serveurs (en %) --}}
    <div class="kpi-card">
        <div class="kpi-icon c-sage"><i class="fa-solid fa-server"></i></div>
        <p class="kpi-label">Serveurs Sains</p>
        <p class="kpi-value">{{ $serverHealthPercent }}%</p>
        <div class="kpi-change {{ $healthyServers == $totalServers ? 'positive' : 'negative' }}">
            {{ $healthyServers }} / {{ $totalServers }} en ligne
        </div>
    </div>

    {{-- Applications (en %) --}}
    <div class="kpi-card">
        <div class="kpi-icon c-teal"><i class="fa-solid fa-window-restore"></i></div>
        <p class="kpi-label">Applications Actives</p>
        <p class="kpi-value">{{ $appHealthPercent }}%</p>
        <div class="kpi-change {{ $activeApps == $totalApps ? 'positive' : 'negative' }}">
            {{ $activeApps }} / {{ $totalApps }} actives
        </div>
    </div>

    {{-- Conformité / Sécurité --}}
        {{-- Conformité / Sécurité --}}
    <div class="kpi-card">
        <div class="kpi-icon c-red"><i class="fa-solid fa-shield-halved"></i></div>
        <p class="kpi-label">Niveau Sécurité</p>
        <p class="kpi-value">{{ $securityScore }}%</p>
        <div class="kpi-change {{ $securityScore >= 70 ? 'positive' : 'negative' }}">
            <i class="fa-solid fa-arrow-{{ $securityScore >= 70 ? 'up' : 'down' }}"></i>
            {{ $securityLevel }}
        </div>
    </div>
</div>

{{-- LIGNE 2 : Les Alertes Critiques & Vue par environnement --}}
<div class="grid-2">
    
    {{-- Alertes Critiques --}}
    <div class="panel">
        <div class="panel-header">
            <p>Alertes Critiques Récentes</p>
            <span class="badge badge-critical">{{ $criticalAlerts->count() }} Actives</span>
        </div>
        <table class="server-table">
            <thead>
                <tr>
                    <th>Statut</th>
                    <th>Événement / Message</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($criticalAlerts as $alert)
                <tr>
                    <td>
                        @if($alert->status == 'open')
                            <span class="badge badge-critical">CRITIQUE</span>
                        @else
                            <span class="badge badge-major">HAUTE</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('alerts.show', $alert->id) }}" style="color: var(--teal); text-decoration: none;">
                            {{ $alert->title }}
                        </a>
                    </td>
                    <td>{{ $alert->created_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" style="text-align:center; padding: 20px; color: var(--text-muted);">
                        Aucune alerte critique. Système opérationnel.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Vue par environnement --}}
    <div class="panel">
        <div class="panel-header">
            <p>Répartition par Environnement</p>
        </div>
        <div class="donut-wrapper">
            <div class="donut-chart">
                <canvas id="envDonutChart"></canvas>
            </div>
            <div class="donut-legend" id="donutLegend">
                <p style="color: var(--text-muted);">Chargement des données...</p>
            </div>
        </div>
    </div>
</div>

{{-- LIGNE 3 : Accès rapides aux listes (MF-2) --}}
<div class="grid-2">
    <a href="{{ route('monitoring.servers.index') }}" class="panel" style="text-decoration: none; color: inherit; transition: transform 0.2s;">
        <div class="panel-header">
            <p>Supervision des Serveurs</p>
            <i class="fa-solid fa-arrow-right"></i>
        </div>
        <p style="color: var(--text-muted); font-size: 13px;">Cliquez pour voir le détail des serveurs, leur CPU, RAM et statut.</p>
    </a>
    
    <a href="{{ route('monitoring.application.index') }}" class="panel" style="text-decoration: none; color: inherit; transition: transform 0.2s;">
        <div class="panel-header">
            <p>Supervision des Applications</p>
            <i class="fa-solid fa-arrow-right"></i>
        </div>
        <p style="color: var(--text-muted); font-size: 13px;">Cliquez pour voir la performance, la sécurité et les logs des applications.</p>
    </a>
</div>
{{-- LIGNE 4 : Carte de Dépendances (MF-38) --}}
<div class="panel dependency-panel" style="margin-top: 24px;">
    <div class="panel-header">
        <p>Carte de Dépendances (Serveurs & Applications)</p>
    </div>
    
    {{-- 1. Le conteneur DOIT avoir une hauteur fixe, sinon il est invisible --}}
    <div id="dependency-network" style="height: 500px; width: 100%; background: var(--page-bg); border-radius: 0 0 24px 24px;"></div>
</div>
{{-- Importation de la librairie Vis.js (CDN) --}}
<script src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>

{{-- Passage des données de PHP au JavaScript externe --}}
<script>
   window.dependencyNodes = {!! $dependencyNodes !!};
    window.dependencyEdges = {!! $dependencyEdges !!};
</script>

@endsection