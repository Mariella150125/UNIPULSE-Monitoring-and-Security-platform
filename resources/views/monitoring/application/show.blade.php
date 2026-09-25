@extends('layout.app')

@section('content')

{{-- SCRIPT PHP EN HAUT POUR LIER OWASP AUX VULNÉRABILITÉS ET LOGS --}}
@php
    // 1. Si on n'a pas trouvé de CVE, on regarde la conformité OWASP A06
    if (empty($vulnerabilities)) {
        $a06Check = collect($security['checks'])->firstWhere('name', 'Dépendances vulnérables');
        if ($a06Check && !$a06Check['is_passed']) {
            $vulnerabilities[] = [
                'cve' => 'OWASP-A06',
                'cvss' => 5.0,
                'severity' => 'MEDIUM',
                'dependency' => $application->name,
                'description' => 'Dépendances vulnérables ou obsolètes détectées par l\'audit de conformité.',
                'published_at' => now()->format('Y-m-d'),
                'link' => $a06Check['guideline'] ?? '#'
            ];
        }
    }

    // 2. Si on n'a pas de logs, on regarde la conformité OWASP A09
    if (empty($logs)) {
        $a09Check = collect($security['checks'])->firstWhere('name', 'Journalisation');
        if ($a09Check && !$a09Check['is_passed']) {
            $logs[] = [
                'level' => 'WARNING',
                'source' => 'OWASP A09',
                'message' => 'Journalisation insuffisante (Non conforme à la règle OWASP A09).',
                'date' => now()
            ];
        }
    }
@endphp

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
    
    {{-- RÈGLE 18 : PANNEAU DES SOURCES DE SURVEILLANCE ACTIVES --}}
    <div class="panel" style="margin-bottom: 24px; border-left: 4px solid var(--dark-teal);">
        <div class="panel-header">
            <p>Sources de Surveillance Actives</p>
        </div>
        <div style="display: flex; gap: 24px; flex-wrap: wrap; padding: 15px 20px; align-items: center;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="status-dot {{ $sources['health_check'] ?? false ? 'online' : 'offline' }}"></span>
                <strong>Health Check (Laravel)</strong>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="status-dot {{ $sources['prometheus'] ?? false ? 'online' : 'offline' }}"></span>
                <strong>Prometheus (Métriques)</strong>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="status-dot {{ $sources['wazuh'] ?? false ? 'online' : 'offline' }}"></span>
                <strong>Wazuh (OS & SCA)</strong>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="status-dot {{ $sources['sca'] ?? false ? 'online' : 'offline' }}"></span>
                <strong>SCA Dépendances</strong>
            </div>
        </div>
    </div>

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
            <p class="kpi-value">{{ count($vulnerabilities) }}</p> {{-- Affichera 1 si A06 est non conforme --}}
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
    <div class="grid-3" style="grid-template-columns: repeat(5, 1fr); gap: 15px;">
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
            <div class="kpi-icon c-red"><i class="fa-solid fa-circle-exclamation"></i></div>
            <p class="kpi-label">Taux d'erreurs</p>
            <p class="kpi-value">{{ $metrics['error_rate'] }}</p>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon c-orange"><i class="fa-solid fa-arrow-trend-up"></i></div>
            <p class="kpi-label">Trafic API</p>
            <p class="kpi-value" style="font-size: 20px;">{{ $metrics['api_traffic'] }}</p>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon c-teal"><i class="fa-solid fa-globe"></i></div>
            <p class="kpi-label">DNS Lookup</p>
            <p class="kpi-value">{{ $metrics['dns_lookup'] }}</p>
        </div>
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
        <div class="panel-header"><p>Conformité OWASP Top 10 (Résultats réels)</p></div>
        <table class="server-table">
            <thead>
                <tr>
                    <th>Catégorie OWASP</th>
                    <th>Contrôle de Sécurité</th>
                    <th>Statut</th>
                    <th>Guideline</th>
                </tr>
            </thead>
            <tbody>
                @foreach($security['checks'] as $check)
                <tr>
                    <td>
                        <strong>{{ $check['owasp'] ?? 'Non catégorisé' }}</strong>
                    </td>
                    <td>{{ $check['name'] }}</td>
                    <td>
                        @if($check['is_passed'])
                            <span class="status-dot online"></span> <span style="color: var(--sage-green);">Conforme</span>
                        @elseif($check['status'] === 'Non évalué')
                            <span class="status-dot" style="background: var(--text-muted);"></span> <span style="color: var(--text-muted);">Non évalué</span>
                        @else
                            <span class="status-dot offline"></span> <span style="color: var(--red);">Non conforme</span>
                        @endif
                        @if(isset($check['status']) && $check['status'] !== 'Conforme' && $check['status'] !== 'Non conforme')
                            <br><small style="color: var(--text-muted);">{{ $check['status'] }}</small>
                        @endif
                    </td>
                    <td>
                        <a href="{{ $check['guideline'] }}" target="_blank" class="usr-btn-1" style="padding: 5px 10px; font-size: 12px; background: {{ $check['is_passed'] ? 'var(--sage-green)' : 'var(--red)' }};">
                            <i class="fa-solid fa-book"></i> Voir Cheat Sheet
                        </a>
                    </td>
                </tr>
                @endforeach
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
        @if(empty($vulnerabilities))
            <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                <i class="fa-solid fa-shield-virus" style="font-size: 32px; margin-bottom: 15px;"></i><br>
                Aucune vulnérabilité détectée. L'application est conforme.
            </div>
        @else
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
        @endif
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
        @if(empty($logs))
            <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                <i class="fa-solid fa-file-lines" style="font-size: 32px; margin-bottom: 15px;"></i><br>
                Aucun log reçu. La journalisation est conforme.
            </div>
        @else
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
        @endif
    </div>
</div>

{{-- Passer les données de PHP à JavaScript proprement pour le graphique --}}
<script>
    window.latencyHistory = @json($latencyHistory ?? []);
    window.availabilityLabels = @json($availabilityLabels ?? []);
    window.availabilityData = @json($availabilityData ?? []);
    window.availTrendLabels = @json($trendLabels ?? []);
    window.availTrendData = @json($availTrend ?? []);
    window.respTrendLabels = @json($trendLabels ?? []);
    window.respTrendData = @json($respTrend ?? []);
</script>

@endsection