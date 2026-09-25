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
        <p class="kpi-value">{{ $onlineServers }}</p>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon c-red"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <p class="kpi-label">Hors ligne / Critique</p>
        <p class="kpi-value">{{ $offlineServers }}</p>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon c-orange"><i class="fa-solid fa-screwdriver-wrench"></i></div>
        <p class="kpi-label">En maintenance</p>
        <p class="kpi-value">{{ $maintenanceServers }}</p>
    </div>
</div>

<div class="grid-2" style="margin-bottom: 24px;">
    {{-- GRAPHIQUE GLOBAL CPU --}}
    <div class="panel">
        <div class="panel-header">
            <p>Utilisation CPU Globale</p>
            <select class="filter-btn" id="global-cpu-range">
                <option value="1h" selected>1 heure</option>
                <option value="24h">24 heures</option>
                <option value="7d">7 jours</option>
                <option value="30d">30 jours</option>
                <option value="90d">90 jours</option>
            </select>
        </div>
        <div style="padding: 20px; height: 300px; position: relative;">
            <canvas id="globalCpuChart"></canvas>
        </div>
    </div>

    {{-- GRAPHIQUE EVOLUTION DES ALERTES SERVEURS --}}
    <div class="panel">
        <div class="panel-header">
            <p>Évolution des Alertes (Serveurs)</p>
            <select class="filter-btn" id="alert-range-select">
                <option value="24h" selected>24 heures</option>
                <option value="7d">7 jours</option>
                <option value="30d">30 jours</option>
            </select>
        </div>
        <div style="padding: 20px; height: 300px; position: relative;">
            <canvas id="serverAlertChart"></canvas>
        </div>
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // ==========================================
        // 1. GESTION DU GRAPHIQUE GLOBAL CPU
        // ==========================================
        const ctx = document.getElementById('globalCpuChart');
        if (ctx) {
            const globalCpuChart = new Chart(ctx, {
                type: 'line',
                data: { labels: [], datasets: [] },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position: 'top', labels: { usePointStyle: true } } },
                    scales: { y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } }, x: { grid: { display: false } } }
                }
            });

            function loadGlobalCpu(range = '1h') {
                fetch(`/monitoring/servers/cpu-history?range=${range}`)
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            globalCpuChart.data.labels = data.labels;
                            globalCpuChart.data.datasets = data.datasets;
                            globalCpuChart.update();
                        }
                    });
            }

            loadGlobalCpu(document.getElementById('global-cpu-range').value);
            document.getElementById('global-cpu-range').addEventListener('change', function() { loadGlobalCpu(this.value); });
        }

        // ==========================================
        // 2. GESTION DU GRAPHIQUE DES ALERTES SERVEURS
        // ==========================================
        const alertCtx = document.getElementById('serverAlertChart');
        if (alertCtx) {
            const serverAlertChart = new Chart(alertCtx, {
                type: 'line',
                data: { 
                    labels: [], 
                    datasets: [{ 
                        label: 'Alertes Serveurs', 
                        data: [], 
                        borderColor: '#c0392b', 
                        backgroundColor: 'rgba(192, 57, 43, 0.1)', 
                        fill: true, 
                        tension: 0.4 
                    }] 
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true, grid: { color: '#eef1ef' } }, x: { grid: { display: false } } },
                    plugins: { legend: { display: true, position: 'top' } }
                }
            });

            function loadAlertHistory(range = '24h') {
                fetch(`/monitoring/servers/alert-history?range=${range}`)
                    .then(r => r.json())
                    .then(data => {
                        if(data.success) {
                            serverAlertChart.data.labels = data.labels;
                            serverAlertChart.data.datasets[0].data = data.data;
                            serverAlertChart.update();
                        }
                    });
            }

            loadAlertHistory(document.getElementById('alert-range-select').value);
            document.getElementById('alert-range-select').addEventListener('change', function() {
                loadAlertHistory(this.value);
            });
        }

        // ==========================================
        // 3. GESTION DES PETITES CARTES (CPU/RAM/DISK)
        // ==========================================
        const panels = document.querySelectorAll('.grafana-panel');
        if (panels.length === 0) return;

        async function loadAllMetrics() {
            panels.forEach(async (panel) => {
                const serverId = panel.dataset.serverId;
                try {
                    const response = await fetch(`/monitoring/servers/${serverId}/metrics`);
                    if (!response.ok) return;
                    const data = await response.json();
                    if (!data.success) return;

                    // Mise à jour du Statut
                    const statusEl = document.getElementById(`status-${serverId}`);
                    if (data.status === 'online') {
                        if (data.cpu !== null && data.cpu >= 90) {
                            statusEl.innerHTML = '<span class="status-dot" style="background: var(--red);"></span> Critique';
                        } else if (data.cpu !== null && data.cpu >= 75) {
                            statusEl.innerHTML = '<span class="status-dot" style="background: var(--orange);"></span> Avertissement';
                        } else {
                            statusEl.innerHTML = '<span class="status-dot online"></span> En ligne';
                        }
                    } else {
                        statusEl.innerHTML = '<span class="status-dot offline"></span> Hors ligne';
                    }

                    // Mise à jour CPU
                    const cpuVal = data.cpu !== null ? `${data.cpu.toFixed(1)} %` : '-- %';
                    const cpuPct = data.cpu !== null ? data.cpu : 0;
                    document.getElementById(`cpu-val-${serverId}`).textContent = cpuVal;
                    const cpuBar = document.getElementById(`cpu-bar-${serverId}`);
                    cpuBar.style.width = `${cpuPct}%`;
                    cpuBar.className = 'metric-bar';
                    if (cpuPct > 80) cpuBar.classList.add('critical');
                    else if (cpuPct > 60) cpuBar.classList.add('warning');

                    // Mise à jour RAM
                    const ramVal = data.memory !== null ? `${data.memory.toFixed(1)} %` : '-- %';
                    const ramPct = data.memory !== null ? data.memory : 0;
                    document.getElementById(`ram-val-${serverId}`).textContent = ramVal;
                    const ramBar = document.getElementById(`ram-bar-${serverId}`);
                    ramBar.style.width = `${ramPct}%`;
                    ramBar.className = 'metric-bar';
                    if (ramPct > 80) ramBar.classList.add('critical');
                    else if (ramPct > 60) ramBar.classList.add('warning');

                    // Mise à jour DISK
                    const diskVal = data.disk !== null ? `${data.disk.toFixed(1)} %` : '-- %';
                    const diskPct = data.disk !== null ? data.disk : 0;
                    document.getElementById(`disk-val-${serverId}`).textContent = diskVal;
                    const diskBar = document.getElementById(`disk-bar-${serverId}`);
                    diskBar.style.width = `${diskPct}%`;
                    diskBar.className = 'metric-bar';
                    if (diskPct > 80) diskBar.classList.add('critical');
                    else if (diskPct > 60) diskBar.classList.add('warning');

                } catch (error) {
                    console.error('Erreur metrics serveur ' + serverId, error);
                }
            });
        }

        loadAllMetrics();
        setInterval(loadAllMetrics, 15000);
    });
</script>

@endsection