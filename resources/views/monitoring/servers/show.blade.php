@extends('layout.app')

@section('content')

<div class="page-title">
    <a href="{{ route('monitoring.servers.index') }}" class="btn btn-cancel" style="margin-bottom: 15px;">
        <i class="fa-solid fa-arrow-left"></i> Retour aux serveurs
    </a>
    <h1>Monitoring : {{ $server->name }}</h1>
    <p>{{ $server->hostname }} - {{ $server->ip_address }}</p>
</div>

<div class="tabs-container">
    <button class="tab-btn active" data-tab="overview">Aperçu</button>
    <button class="tab-btn" data-tab="metrics">Métriques</button>
    <button class="tab-btn" data-tab="security">Sécurité</button>
    <button class="tab-btn" data-tab="logs">Logs</button>
</div>

<!-- ONGLET 1 : APERÇU -->
<div class="tab-content active" id="tab-overview">
    <div class="grid-3" style="gap: 15px; margin-bottom: 24px;">
        <div class="kpi-card">
            <div class="kpi-icon c-sage"><i class="fa-solid fa-circle-check"></i></div>
            <p class="kpi-label">Statut Système</p>
            <p class="kpi-value" style="font-size: 20px;">En ligne</p>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon c-teal"><i class="fa-solid fa-clock"></i></div>
            <p class="kpi-label">Uptime (MF-8)</p>
            <p class="kpi-value" style="font-size: 20px;">{{ $uptime ?? '—' }}</p>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon c-red"><i class="fa-solid fa-shield-halved"></i></div>
            <p class="kpi-label">Score CIS (MF-9)</p>
            <p class="kpi-value">{{ $security['cis_score'] ?? 0 }}%</p>
        </div>
    </div>

    <div class="grid-2">
        <div class="panel">
            <div class="panel-header"><p>Informations système</p></div>
            <div class="details-grid">
                <div class="detail-item">
                    <span class="detail-label">Adresse IP</span>
                    <span class="detail-value">{{ $server->ip_address }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">OS</span>
                    <span class="detail-value">{{ $server->os }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Environnement</span>
                    <span class="detail-value">{{ $server->environment }}</span>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header"><p>Métriques en temps réel</p></div>
            <div class="monitor-card" data-server-id="{{ $server->id }}">
                <div class="monitor-header">
                    <h3>État actuel</h3>
                    <span class="monitor-status" id="server-status">Chargement...</span>
                </div>
                <div class="metric-grid" style="grid-template-columns: repeat(4, 1fr); gap: 15px;">
                    <div class="metric-box">
                        <span class="metric-label">CPU</span>
                        <strong class="metric-value" id="cpu-value">-- %</strong>
                        <div class="metric-bar-container" style="margin-top: 10px;">
                            <div class="metric-bar" id="cpu-bar" style="width: 0%"></div>
                        </div>
                    </div>
                    <div class="metric-box">
                        <span class="metric-label">RAM</span>
                        <strong class="metric-value" id="memory-value">-- %</strong>
                        <div class="metric-bar-container" style="margin-top: 10px;">
                            <div class="metric-bar" id="ram-bar" style="width: 0%"></div>
                        </div>
                    </div>
                    <div class="metric-box">
                        <span class="metric-label">DISK</span>
                        <strong class="metric-value" id="disk-value">-- %</strong>
                        <div class="metric-bar-container" style="margin-top: 10px;">
                            <div class="metric-bar" id="disk-bar" style="width: 0%; background: var(--orange);"></div>
                        </div>
                    </div>
                    <div class="metric-box">
                        <span class="metric-label">NETWORK</span>
                        <strong class="metric-value" id="network-value" style="color: var(--text-muted);">-- MB/s</strong>
                        <div style="margin-top: 10px; font-size: 12px; color: var(--text-muted);">
                            Trafic en temps réel
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="panel" style="margin-top: 24px;">
        <div class="panel-header">
            <p>Applications hébergées ({{ $server->applications->count() }})</p>
        </div>
        @if($server->applications->isNotEmpty())
            <table class="server-table">
                <thead>
                    <tr>
                        <th>Nom de l'application</th>
                        <th>Environnement</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($server->applications as $app)
                        <tr>
                            <td><strong>{{ $app->name }}</strong></td>
                            <td>
                                <span class="env-badge env-{{ $app->environment ?? 'dev' }}">
                                    {{ ucfirst($app->environment ?? 'N/A') }}
                                </span>
                            </td>
                            <td><span class="status-dot online"></span> Actif</td>
                            <td>
                                <a href="{{ route('monitoring.application.show', $app->id) }}" class="usr-btn-1">
                                    <i class="fa-solid fa-eye"></i> Voir le monitoring
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="text-align: center; padding: 30px; color: var(--text-muted);">
                Aucune application hébergée sur ce serveur.
            </div>
        @endif
    </div>
</div>

<!-- ONGLET 2 : MÉTRIQUES -->
<div class="tab-content" id="tab-metrics">
    <div class="source-indicator">
        <i class="fa-solid fa-database" style="color: var(--dark-teal);"></i>
        <span>Source des données : Prometheus (Historique)</span>
    </div>

    {{-- Graphique CPU --}}
    <div class="panel">
        <div class="panel-header">
            <p>Utilisation CPU</p>
            <select class="filter-btn" data-chart-range>
                <option value="30min">30 dernières minutes</option>
                <option value="24h" selected>24 dernières heures</option>
                <option value="48h">48 dernières heures</option>
                <option value="7d">7 derniers jours</option>
                <option value="30d">30 derniers jours</option>
            </select>
        </div>
        <div class="alertChart" style="height: 250px; position: relative;">
            <canvas id="serverCpuChart"></canvas>
        </div>
    </div>

    {{-- Graphique RAM --}}
    <div class="panel" style="margin-top: 24px;">
        <div class="panel-header">
            <p>Consommation RAM</p>
            <select class="filter-btn" data-chart-range>
                <option value="30min">30 dernières minutes</option>
                <option value="24h" selected>24 dernières heures</option>
                <option value="48h">48 dernières heures</option>
                <option value="7d">7 derniers jours</option>
                <option value="30d">30 derniers jours</option>
            </select>
        </div>
        <div class="alertChart" style="height: 250px; position: relative;">
            <canvas id="serverRamChart"></canvas>
        </div>
    </div>

    {{-- Graphique DISK --}}
    <div class="panel" style="margin-top: 24px;">
        <div class="panel-header">
            <p>Utilisation Disque</p>
            <select class="filter-btn" data-chart-range>
                <option value="30min">30 dernières minutes</option>
                <option value="24h" selected>24 dernières heures</option>
                <option value="48h">48 dernières heures</option>
                <option value="7d">7 derniers jours</option>
                <option value="30d">30 derniers jours</option>
            </select>
        </div>
        <div class="alertChart" style="height: 250px; position: relative;">
            <canvas id="serverDiskChart"></canvas>
        </div>
    </div>

    {{-- Graphique NETWORK --}}
    <div class="panel" style="margin-top: 24px;">
        <div class="panel-header">
            <p>Trafic Réseau</p>
            <select class="filter-btn" data-chart-range>
                <option value="30min">30 dernières minutes</option>
                <option value="24h" selected>24 dernières heures</option>
                <option value="48h">48 dernières heures</option>
                <option value="7d">7 derniers jours</option>
                <option value="30d">30 derniers jours</option>
            </select>
        </div>
        <div class="alertChart" style="height: 250px; position: relative;">
            <canvas id="serverNetworkChart"></canvas>
        </div>
    </div>
</div>

<!-- ONGLET 3 : SÉCURITÉ -->
<div class="tab-content" id="tab-security">
    <div class="grid-2">
        <div class="panel">
            <div class="panel-header"><p>Analyse de Sécurité (Wazuh SCA)</p></div>
            <div class="details-grid">
                <div class="detail-item">
                    <span class="detail-label">Score Global CIS (MF-9)</span>
                    <span class="detail-value" style="color: var(--sage-green); font-weight: bold;">{{ $security['cis_score'] ?? 0 }}%</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Intégrité Fichiers (FIM - MF-10)</span>
                    @if(($security['fim_alerts'] ?? 0) > 0)
                        <span class="detail-value" style="color: var(--red); font-weight: bold;">{{ $security['fim_alerts'] }} modification(s)</span>
                    @else
                        <span class="detail-value" style="color: var(--sage-green); font-weight: bold;">Aucune modification</span>
                    @endif
                </div>
                <div class="detail-item">
                    <span class="detail-label">État de l'agent Wazuh</span>
                    <span class="detail-value"><span class="status-dot online"></span> {{ ucfirst($security['agent_status'] ?? 'N/A') }}</span>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header"><p>Vulnérabilités & Expositions (MF-7)</p></div>
            <table class="server-table">
                <thead>
                    <tr><th>Type</th><th>Détails</th><th>Statut</th></tr>
                </thead>
                <tbody>
                    @foreach(($security['stopped_services'] ?? []) as $service)
                    <tr>
                        <td>Service arrêté</td>
                        <td>{{ ucfirst($service) }}</td>
                        <td><span class="badge badge-critical">CRITICAL</span></td>
                    </tr>
                    @endforeach
                    @foreach(($security['open_ports'] ?? []) as $port => $name)
                    <tr>
                        <td>Port exposé</td>
                        <td>{{ $port }} ({{ $name }})</td>
                        <td><span class="badge badge-major">WARNING</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ONGLET 4 : LOGS -->
<div class="tab-content" id="tab-logs">
    <div class="panel">
        <div class="panel-header">
            <p>Logs Système & Nginx</p>
            <select class="filter-btn"><option>Tous niveaux</option></select>
        </div>
        <div class="terminal-log">
            @foreach(($logs ?? []) as $log)
            <div class="terminal-line">
                <span class="term-time">{{ $log['date']->format('H:i:s') }}</span>
                @if($log['level'] == 'ERROR')
                    <span class="term-level term-error">[ERROR]</span>
                @elseif($log['level'] == 'WARNING')
                    <span class="term-level term-warn">[WARN]</span>
                @else
                    <span class="term-level term-info">[INFO]</span>
                @endif
                <span class="term-source">{{ $log['source'] }}</span>
                <span class="term-message">{{ $log['message'] }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

<script>
    window.serverTimeLabels = @json($timeLabels);
    window.serverCpuHistory = @json($cpuHistory);
    window.serverRamHistory = @json($ramHistory);
    window.serverDiskHistory = @json($diskHistory);
    window.serverNetworkHistory = @json($networkHistory);
</script>

@endsection