@extends('layout.app')

@section('content')

<div class="page-title">
    <a href="{{ route('monitoring.application.index') }}" class="btn btn-cancel" style="margin-bottom: 15px;">
        <i class="fa-solid fa-arrow-left"></i> Retour aux applications
    </a>
    <h1>{{ $application->name }}</h1>
    <p>{{ $application->identifiant_genere }} - {{ ucfirst($application->environment) }}</p>
</div>

<!-- Navigation par Onglets -->
<div class="tabs-container">
    <button class="tab-btn active" data-tab="overview">Aperçu</button>
    <button class="tab-btn" data-tab="performance">Performance</button>
    <button class="tab-btn" data-tab="security">Sécurité</button>
    <button class="tab-btn" data-tab="dependencies">Dépendances</button>
    <button class="tab-btn" data-tab="logs">Logs</button>
</div>

<!-- ========================================== -->
<!-- ONGLET 1 : APERÇU                          -->
<!-- ========================================== -->
<div class="tab-content active" id="tab-overview">
    
    {{-- Les 3 KPI en pleine largeur (Façon Grafana) --}}
    <div class="grid-3" style="gap: 15px; margin-bottom: 24px;">
        <div class="kpi-card">
            <div class="kpi-icon c-sage"><i class="fa-solid fa-gauge-high"></i></div>
            <p class="kpi-label">Temps de réponse</p>
            <p class="kpi-value">{{ $metrics['response_time'] }}</p>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon c-teal"><i class="fa-solid fa-shield-halved"></i></div>
            <p class="kpi-label">Score Sécurité</p>
            <p class="kpi-value">{{ $security['global_score'] }}/100</p>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon c-red"><i class="fa-solid fa-bug"></i></div>
            <p class="kpi-label">Vulnérabilités</p>
            <p class="kpi-value">{{ count($vulnerabilities) }}</p>
        </div>
    </div>

    {{-- Les informations détaillées en dessous --}}
    <div class="panel">
        <div class="panel-header"><p>Informations Générales</p></div>
        <div class="details-grid">
            <div class="detail-item">
                <span class="detail-label">Statut Système</span>
                <span class="detail-value">
                    <span class="status-dot online"></span> {{ ucfirst($application->status) }}
                </span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Criticité (Auto)</span>
                <span class="detail-value">
                    @php
                        $critStyles = [
                            'low'      => ['color' => 'var(--text-muted)', 'label' => 'Basse'],
                            'medium'   => ['color' => 'var(--sage-green)', 'label' => 'Moyenne'],
                            'high'     => ['color' => 'var(--orange)', 'label' => 'Haute'],
                            'critical' => ['color' => 'var(--red)', 'label' => 'Critique'],
                        ];
                        $crit = $critStyles[$application->criticality] ?? ['color' => 'var(--text-muted)', 'label' => 'Non définie'];
                    @endphp
                    <strong style="color: {{ $crit['color'] }};">{{ $crit['label'] }}</strong>
                </span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Backend</span>
                <span class="detail-value">{{ $application->framework }} ({{ $application->language }})</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Frontend</span>
                <span class="detail-value">{{ $application->frontend_framework ?? 'N/A' }} ({{ $application->frontend_language ?? 'N/A' }})</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Base de données</span>
                <span class="detail-value">{{ $application->database_type ?? 'Aucune' }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">URL API</span>
                <span class="detail-value">{{ $application->url ?? '—' }}</span>
            </div>
        </div>
    </div>

</div>

<!-- ========================================== -->
<!-- ONGLET 2 : PERFORMANCE (Façon Grafana)     -->
<!-- ========================================== -->
<div class="tab-content" id="tab-performance">
    
    {{-- Indicateur de la source des données (Prometheus ou Health-Check) --}}
    <div style="margin-bottom: 15px; padding: 10px 15px; background: var(--page-bg); border-radius: 8px; display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid fa-database" style="color: var(--dark-teal);"></i>
        <span style="font-size: 13px; font-weight: 600;">Source des données : {{ $metrics['source'] ?? 'N/A' }}</span>
    </div>

    <div class="grid-3">
        <div class="kpi-card">
            <div class="kpi-icon c-sage"><i class="fa-solid fa-heart-pulse"></i></div>
            <p class="kpi-label">Disponibilité</p>
            <p class="kpi-value">{{ $metrics['availability'] }}</p>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon c-teal"><i class="fa-solid fa-clock"></i></div>
            <p class="kpi-label">Temps de réponse</p>
            <p class="kpi-value">{{ $metrics['response_time'] }}</p>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon c-orange"><i class="fa-solid fa-globe"></i></div>
            <p class="kpi-label">DNS Lookup</p>
            <p class="kpi-value">{{ $metrics['dns_lookup'] }}</p>
        </div>
    </div>

    {{-- LE VRAI GRAPHIQUE FAÇON GRAFANA AVEC FILTRE --}}
    <div class="panel" style="margin-top: 24px;">
        <div class="panel-header">
            <p>Latence et Temps de réponse ({{ $range == '7d' ? '7 derniers jours' : ($range == '30d' ? '30 derniers jours' : '24 dernières heures') }})</p>
            <select class="filter-btn" id="latency-range-select">
                <option value="24h" {{ $range == '24h' ? 'selected' : '' }}>Dernières 24h</option>
                <option value="7d" {{ $range == '7d' ? 'selected' : '' }}>7 derniers jours</option>
                <option value="30d" {{ $range == '30d' ? 'selected' : '' }}>30 derniers jours</option>
            </select>
        </div>
        <div class="alertChart" style="height: 300px; position: relative;">
            <canvas id="appResponseTimeChart"></canvas>
        </div>
    </div>

    <div class="panel" style="margin-top: 24px;">
        <div class="panel-header"><p>Détails HTTP & Probes (Prometheus / Health-Check)</p></div>
        <table class="server-table">
            <thead>
                <tr>
                    <th>Métrique</th>
                    <th>Valeur</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Statut HTTP</td>
                    <td><span class="badge badge-critical" style="background: var(--sage-green); color: white;">{{ $metrics['http_status'] }}</span></td>
                </tr>
                <tr>
                    <td>Version HTTP</td>
                    <td>{{ $metrics['http_version'] }}</td>
                </tr>
                <tr>
                    <td>Global Probe Duration (GPD)</td>
                    <td>{{ $metrics['gpd'] }}</td>
                </tr>
                <tr>
                    <td>Probe Duration (PD)</td>
                    <td>{{ $metrics['pd'] }}</td>
                </tr>
                <tr>
                    <td>Average Probe Duration (APD)</td>
                    <td>{{ $metrics['apd'] }}</td>
                </tr>
                <tr>
                    <td>État des services</td>
                    <td><span class="status-dot online"></span> {{ $metrics['services_status'] }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ========================================== -->
<!-- ONGLET 3 : SÉCURITÉ                        -->
<!-- ========================================== -->
<div class="tab-content" id="tab-security">
    <div class="grid-3">
        <div class="kpi-card">
            <div class="kpi-icon c-teal"><i class="fa-solid fa-shield-halved"></i></div>
            <p class="kpi-label">Score Global Pondéré</p>
            <p class="kpi-value">{{ $security['global_score'] }}/100</p>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon c-sage"><i class="fa-solid fa-check-double"></i></div>
            <p class="kpi-label">Conformité OWASP</p>
            <p class="kpi-value">{{ $security['owasp_score'] }}</p>
        </div>
        <div class="kpi-card">
            @if($security['ssl_status'] == 'VLD')
                <div class="kpi-icon c-sage"><i class="fa-solid fa-lock"></i></div>
            @else
                <div class="kpi-icon c-red"><i class="fa-solid fa-lock-open"></i></div>
            @endif
            <p class="kpi-label">Certificat SSL</p>
            <p class="kpi-value">{{ $security['ssl_status'] }}</p>
        </div>
    </div>

    <div class="panel" style="margin-top: 24px;">
        <div class="panel-header"><p>Détails du Certificat SSL</p></div>
        <div class="details-grid">
            <div class="detail-item">
                <span class="detail-label">État du Certificat</span>
                <span class="detail-value">
                    @php
                        $sslLabel = match($security['ssl_status']) {
                            'VLD' => 'Valide',
                            'EXP' => 'Expiré',
                            'SPD' => 'Bientôt expiré',
                            'RVK' => 'Révoqué',
                            'PND' => 'En attente',
                            default => 'Inconnu'
                        };
                    @endphp
                    {{ $security['ssl_status'] }} ({{ $sslLabel }})
                </span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Date d'expiration</span>
                <span class="detail-value">{{ $security['ssl_expiry_date'] }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Jours restants</span>
                <span class="detail-value">
                    {{ is_null($security['ssl_days_left']) ? '—' : $security['ssl_days_left'] . ' jours' }}
                </span>
            </div>
        </div>
    </div>

    <div class="panel" style="margin-top: 24px;">
        <div class="panel-header"><p>Conformité OWASP Top 10 (MF-7, MF-8, MF-10)</p></div>
        <table class="server-table">
            <thead>
                <tr>
                    <th>Catégorie OWASP</th>
                    <th>Statut</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>A01: Broken Access Control</td>
                    <td><span class="status-dot online"></span> Conforme</td>
                    <td><a href="#" class="usr-btn-1" style="padding: 5px 10px; font-size: 12px;">Voir détail</a></td>
                </tr>
                <tr>
                    <td>A02: Cryptographic Failures</td>
                    <td><span class="status-dot offline"></span> Non Conforme</td>
                    <td><a href="#" class="usr-btn-1" style="padding: 5px 10px; font-size: 12px; background: var(--red);">Voir détail</a></td>
                </tr>
                <tr>
                    <td>A03: Injection</td>
                    <td><span class="status-dot online"></span> Conforme</td>
                    <td><a href="#" class="usr-btn-1" style="padding: 5px 10px; font-size: 12px;">Voir détail</a></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ========================================== -->
<!-- ONGLET 4 : DÉPENDANCES                     -->
<!-- ========================================== -->
<div class="tab-content" id="tab-dependencies">
    <div class="panel">
        <div class="panel-header">
            <p>Analyse des Vulnérabilités (CVE / CVSS)</p>
            <button class="filter-btn" id="sort-cvss-btn">
                <i class="fa-solid fa-arrow-down-9-1"></i> Trier par CVSS
            </button>
        </div>
        <table class="server-table" id="vulns-table">
            <thead>
                <tr>
                    <th>CVE</th>
                    <th>Dépendance</th>
                    <th>CVSS</th>
                    <th>Criticité</th>
                    <th>Description</th>
                    <th>Date</th>
                    <th>Lien NVD</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vulnerabilities as $vuln)
                <tr data-cvss="{{ $vuln['cvss'] }}">
                    <td><strong>{{ $vuln['cve'] }}</strong></td>
                    <td>{{ $vuln['dependency'] }}</td>
                    <td><strong>{{ $vuln['cvss'] }}</strong></td>
                    <td>
                        @if($vuln['severity'] == 'HIGH' || $vuln['cvss'] >= 7.0)
                            <span class="badge badge-critical">HIGH</span>
                        @elseif($vuln['severity'] == 'MEDIUM' || $vuln['cvss'] >= 4.0)
                            <span class="badge badge-major">MEDIUM</span>
                        @else
                            <span class="badge">LOW</span>
                        @endif
                    </td>
                    <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $vuln['description'] }}</td>
                    <td>{{ $vuln['published_at'] }}</td>
                    <td><a href="{{ $vuln['link'] }}" target="_blank" class="dropdown-item"><i class="fa-solid fa-up-right-from-square"></i> NVD</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- ========================================== -->
<!-- ONGLET 5 : LOGS                            -->
<!-- ========================================== -->
<div class="tab-content" id="tab-logs">
    <div class="panel">
        <div class="panel-header">
            <p>Logs Centralisés</p>
            <select class="filter-btn" id="log-filter">
                <option value="">Tous les niveaux</option>
                <option value="ERROR">Erreur</option>
                <option value="WARNING">Warning</option>
                <option value="INFO">Info</option>
            </select>
        </div>
        <table class="server-table" id="logs-table">
            <thead>
                <tr>
                    <th>Niveau</th>
                    <th>Source</th>
                    <th>Message</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                <tr data-level="{{ $log['level'] }}">
                    <td>
                        @if($log['level'] == 'ERROR')
                            <span class="badge badge-critical">ERROR</span>
                        @elseif($log['level'] == 'WARNING')
                            <span class="badge badge-major">WARNING</span>
                        @else
                            <span class="badge" style="background: var(--page-bg);">INFO</span>
                        @endif
                    </td>
                    <td>{{ $log['source'] }}</td>
                    <td>{{ $log['message'] }}</td>
                    <td>{{ $log['date']->format('d/m/Y H:i:s') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Passer les données de PHP à JavaScript proprement pour le graphique --}}
<script>
    window.latencyHistory = @json($latencyHistory ?? []);
</script>

@endsection