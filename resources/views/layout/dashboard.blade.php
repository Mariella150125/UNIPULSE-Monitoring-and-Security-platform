@extends('layout.app')
@section('content')

    <div class="page-title">
        <h1><span class="pacifico"> Bonjour,</span> 
             {{ Auth::user()->name }}👋
        </h1>
    </div>

    {{-- Rangée de 12 KPI, une seule ligne, défilement horizontal --}}
    <div class="kpi-row">
        <div class="kpi-card">
            <div class="kpi-icon c-sage"><i class="fa-solid fa-eye"></i></div>
            <p class="kpi-label">Apps supervisées</p><p class="kpi-value">{{ $totalApps }}</p>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon c-sage"><i class="fa-solid fa-satellite-dish"></i></div>
            <p class="kpi-label">Serveurs supervisés</p><p class="kpi-value">{{ $totalServers }}</p>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon c-teal"><i class="fa-solid fa-users"></i></div>
            <p class="kpi-label">Utilisateurs</p><p class="kpi-value">{{ $totalUsers }}</p>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon c-red"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <p class="kpi-label">Alertes critiques</p><p class="kpi-value">{{ $criticalAlerts }}</p>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon c-orange"><i class="fa-solid fa-circle-exclamation"></i></div>
            <p class="kpi-label">Alertes majeures</p><p class="kpi-value">{{ $majorAlerts }}</p>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon c-muted"><i class="fa-solid fa-circle-info"></i></div>
            <p class="kpi-label">Agents Actifs</p><p class="kpi-value">{{ $activeAgents }}</p>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon c-orange"><i class="fa-solid fa-folder-open"></i></div>
            <p class="kpi-label">Incidents ouverts</p><p class="kpi-value">{{ $openIncidents }}</p>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon c-sage"><i class="fa-solid fa-circle-check"></i></div>
            <p class="kpi-label">Incidents résolus</p><p class="kpi-value">{{ $resolvedIncidents }}</p>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon c-teal"><i class="fa-solid fa-shield-halved"></i></div>
            <p class="kpi-label">Score sécurité</p><p class="kpi-value">{{ $securityScore }}%</p>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon c-sage"><i class="fa-solid fa-clipboard-check"></i></div>
            <p class="kpi-label">Score conformité</p><p class="kpi-value">{{ $complianceScore }}%</p>
        </div>
    </div>

    {{-- Graphiques conformes au SRS + derniers événements --}}
    <div class="grid-3">
        <div class="panel">
            <div class="panel-header">
                <p>Évolution des alertes</p>
                <form action="{{ route('dashboard') }}" method="GET" onchange="this.submit()">
                    <select name="period" class="period-btn">
                        <option value="7" {{ $period == '7' ? 'selected' : '' }}>7 derniers jours</option>
                        <option value="30" {{ $period == '30' ? 'selected' : '' }}>30 derniers jours</option>
                        <option value="90" {{ $period == '90' ? 'selected' : '' }}>90 derniers jours</option>
                    </select>
                </form>
            </div>
             <div class="alertChart">
                <canvas id="alertChart"></canvas>
             </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <p>Évolution du score de sécurité</p>
                <form action="{{ route('dashboard') }}" method="GET" onchange="this.submit()">
                    <select name="period" class="period-btn">
                        <option value="7" {{ $period == '7' ? 'selected' : '' }}>7 derniers jours</option>
                        <option value="30" {{ $period == '30' ? 'selected' : '' }}>30 derniers jours</option>
                        <option value="90" {{ $period == '90' ? 'selected' : '' }}>90 derniers jours</option>
                    </select>
                </form>
            </div>
            <div class="securityChart">
                <canvas id="dashboardSecurityChart"></canvas>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <p>Derniers événements</p>
                <a href="{{ route('settings.audit-logs.index') }}" class="link-see-all">Voir tous</a>
            </div>
            <ul class="event-list">
                @foreach($recentEvents as $event)
                    <li>
                        <span>{{ $event->user?->name ?? 'Système' }} - {{ $event->details ?? $event->action }}</span>
                        <time>{{ $event->created_at->format('H:i') }}</time>
                    </li>
                @endforeach
                @if($recentEvents->isEmpty())
                    <li style="color: var(--text-muted); text-align: center; padding: 20px;">Aucun événement récent.</li>
                @endif
            </ul>
        </div>
    </div>

    {{-- Santé des serveurs / Etat des agents / Actions rapides --}}
    <div class="grid-3">
        <div class="panel">
            <div class="panel-header">
                <p>Santé des serveurs</p>
                <a href="{{ route('server.index') }}" class="link-see-all">Voir tous</a>
            </div>
            <table class="server-table">
                <thead>
                    <tr><th>Serveur</th><th>Statut</th></tr>
                </thead>
                <tbody>
                    @foreach($serversHealth as $server)
                    <tr>
                        <td><strong>{{ $server->hostname }}</strong></td>
                        <td>
                            @if($server->global_status == 'healthy') <span class="status-dot online"></span> En ligne

                            @elseif($server->global_status == 'critical') <span class="status-dot offline"></span> Critique
                            @else <span class="status-dot warning"></span> Avertissement @endif
                        </td>
                    </tr>
                    @endforeach
                    @if($serversHealth->isEmpty())
                        <tr><td colspan="2" style="text-align:center; color:var(--text-muted);">Aucun serveur.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="panel">
            <div class="panel-header">
                <p>Alertes critiques récentes</p>
                <a href="{{ route('alerts.index') }}" class="link-see-all">Voir tous</a>
            </div>
            <ul class="alert-list">
                @foreach($recentAlerts as $alert)
                <li>
                    <i class="fa-solid fa-triangle-exclamation c-red"></i>
                    <div>
                        <strong>{{ $alert->title }}</strong>
                        <span>Source : {{ strtoupper($alert->source) }}</span>
                    </div>
                    <span class="badge badge-critical">{{ ucfirst($alert->priority) }}</span>
                </li>
                @endforeach
                @if($recentAlerts->isEmpty())
                    <li style="color: var(--text-muted); text-align: center; padding: 20px;">Aucune alerte critique.</li>
                @endif
            </ul>
        </div>

        <div class="panel">
            <div class="panel-header"><p>Actions rapides</p></div>
            <div class="quick-actions">
                {{-- OUVERTURE DES MODALES VIA DATA-MODAL-OPEN --}}
                <button data-modal-open="server-modal"><i class="fa-solid fa-server"></i> Nouveau serveur</button>
                <button data-modal-open="application-modal"><i class="fa-solid fa-plus"></i> Nouvelle application</button>
                <button onclick="window.location.href='{{ route('reports.create') }}'"><i class="fa-solid fa-file-circle-plus"></i> Créer un rapport</button>
                <button onclick="window.location.href='{{ route('settings') }}'"><i class="fa-solid fa-gear"></i> Paramètres</button>
            </div>
        </div>
    </div>

    <p class="sync-time">Dernière synchronisation : {{ now()->format('H:i') }}</p>

    {{-- INCLUSION DES MODALES POUR QU'ELLES SOIENT DISPONIBLES SUR LE DASHBOARD --}}
    @include('administration.servers.server-modal')
    @include('administration.applis.appli-modal')

    {{-- TRANSMISSION DES DONNÉES AU FICHIER LOGIN.JS SANS ÉCRIRE DE LOGIQUE ICI --}}
    <script>
        window.dashboardAlertLabels = @json($alertLabels);
        window.dashboardAlertData = @json($alertData);
        window.dashboardSecurityLabels = @json($securityLabels);
        window.dashboardSecurityData = @json($securityData);
    </script>

@endsection