@extends('layout.app')

@section('content')

<div class="page-title">
    <h1>Centre d'Alertes</h1>
    <p>Surveillance temps réel, corrélation et historique des incidents.</p>
</div>

<div style="display: flex; justify-content: flex-end; margin-bottom: 15px;">
    <form action="{{ route('alerts.index') }}" method="GET" id="periodForm" style="display: flex; gap: 10px; align-items: center;">
        <select name="period" id="periodSelect" onchange="toggleCustomDates()" class="form-control" style="width: auto;">
            <option value="24" {{ $period == '24' ? 'selected' : '' }}>24 dernières heures</option>
            <option value="7" {{ $period == '7' ? 'selected' : '' }}>7 derniers jours</option>
            <option value="30" {{ $period == '30' ? 'selected' : '' }}>30 derniers jours</option>
            <option value="90" {{ $period == '90' ? 'selected' : '' }}>90 derniers jours</option>
            <option value="custom" {{ $period == 'custom' ? 'selected' : '' }}>Période personnalisée</option>
        </select>
        
        <div id="customDates" style="display: {{ $period == 'custom' ? 'flex' : 'none' }}; gap: 10px;">
            <input type="date" name="start_date" value="{{ $startDate ?? '' }}" class="form-control" required>
            <input type="date" name="end_date" value="{{ $endDate ?? '' }}" class="form-control" required>
        </div>

        <button type="submit" id="filterBtn" class="btn btn-primary" style="display: {{ $period == 'custom' ? 'block' : 'none' }};">
            <i class="fa-solid fa-filter"></i>
        </button>
    </form>
</div>

{{-- KPIs --}}
<div class="usr-kpi-row">
    <div class="kpi-card">
        <div class="kpi-icon c-red"><i class="fa-solid fa-circle-exclamation"></i></div>
        <p class="kpi-label">Alertes Actives</p>
        <p class="kpi-value">{{ $stats['active'] }}</p>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon c-orange"><i class="fa-solid fa-fire"></i></div>
        <p class="kpi-label">Critiques</p>
        <p class="kpi-value">{{ $stats['critical'] }}</p>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon c-sage"><i class="fa-solid fa-check-circle"></i></div>
        <p class="kpi-label">Résolues (Aujourd'hui)</p>
        <p class="kpi-value">{{ $stats['resolved'] }}</p>
    </div>
     <div class="kpi-card">
        <div class="kpi-icon c-teal"><i class="fa-solid fa-stopwatch"></i></div>
        <p class="kpi-label">Temps Moy. Résolution</p>
        <p class="kpi-value">{{ $stats['mttr'] }} <span style="font-size: 14px; color: var(--text-muted);">min</span></p>
    </div>
</div>

{{-- LIGNE 1 : Demi-cercle + Evolution --}}
<div class="grid-2" style="margin-bottom: 24px;">
    
    {{-- CARTE DEMI-CERCLE (Statuts) --}}
    <div class="panel">
        <div class="panel-header"><p>Répartition des Statuts</p></div>
        <div style="padding: 20px; height: 260px; position: relative;">
            <canvas id="statusHalfCircleChart"></canvas>
        </div>
    </div>

    {{-- CARTE EVOLUTION (Avec filtre catégorie) --}}
    <div class="panel">
        <div class="panel-header">
            <p>Évolution des Alertes  <small style="color:var(--text-muted);">({{ $periodText }})</small></p>
            <div class="filter-btn" style="display: flex; gap: 5px;">
                <button class="filter-btn" onclick="filterChartCategory('all')">Toutes</button>
                <button class="filter-btn" onclick="filterChartCategory('secu')">Sécurité</button>
                <button class="filter-btn" onclick="filterChartCategory('infra')">Infra</button>
            </div>
        </div>
        <div style="padding: 20px; height: 300px; position: relative;">
            <canvas id="evolutionChart"></canvas>
        </div>
    </div>
</div>

{{-- LIGNE 2 : Diagramme en bandes (Sources majeures) --}}
<div class="panel" style="margin-bottom: 24px;">
    <div class="panel-header">
        <p>Sources d'Alertes Majeures (Top 5 Ressources)</p>
    </div>
    <div style="padding: 20px; height: 300px; position: relative;">
        <canvas id="majorSourcesChart"></canvas>
    </div>
</div>

{{-- TABLEAU 1 : ALERTES ACTIVES --}}
<div class="panel">
    <div class="panel-header">
        <p>Alertes Actives & Corrélées</p>
    </div>
    <table class="server-table" id="activeAlertsTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Priorité</th>
                <th>Ressource</th>
                <th>Source</th>
                <th>Description</th>
                <th>État</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($alerts as $alert)
            <tr style="border-left: 4px solid {{ $alert->priority === 'critical' ? 'var(--red)' : 'var(--orange)' }};">
                <td><strong>{{ $alert->code ?? 'ALR-'.$alert->id }}</strong></td>
                <td>
                    @if($alert->priority == 'critical') <span class="badge badge-critical">CRITIQUE</span>
                    @elseif($alert->priority == 'high') <span class="badge badge-major">HAUTE</span>
                    @else <span class="badge">MOYENNE</span> @endif
                </td>
                <td>
                    {{-- NOM DE L'ALERTE CLIQUABLE --}}
                    <a href="{{ route('alerts.show', $alert->id) }}" style="color: var(--teal); text-decoration: none;">
                        <strong>{{ $alert->title }}</strong>
                    </a>
                </td>
                <td><span class="badge">{{ strtoupper($alert->source) }}</span></td>
                <td>{{ $alert->description }}</td>
                <td>
                    @if($alert->status == 'open') <span class="status-dot online" style="background:var(--red);"></span> Ouverte
                    @elseif($alert->status == 'acknowledged') <span class="status-dot online" style="background:var(--orange);"></span> Acquittée
                    @endif
                </td>
                <td>
                    <div class="action-dropdown">
                        <button class="icon-btn action-dropdown-toggle" title="Actions">
                            <i class="fa-solid fa-ellipsis-vertical"></i>
                        </button>
                        <div class="action-dropdown-menu">
                            
                            <a href="{{ route('alerts.show', $alert->id) }}" class="dropdown-item">
                                <i class="fa-solid fa-eye"></i> Voir détail
                            </a>

                            @if($alert->status == 'open')
                            <form action="{{ route('alerts.acknowledge', $alert->id) }}" method="POST">
                                @csrf 
                                @method('PUT')
                                <button type="submit" class="dropdown-item">
                                    <i class="fa-solid fa-check" style="color: var(--sage-green);"></i> Acquitter
                                </button>
                            </form>
                            @endif

                            @if($alert->status != 'closed')
                            <a href="{{ route('alerts.show', $alert->id) }}" class="dropdown-item">
                                <i class="fa-solid fa-user-plus"></i> Assigner
                            </a>
                            @endif

                            @if($alert->status != 'closed')
                            <div class="dropdown-divider"></div>
                            @endif

                            @if($alert->status != 'closed')
                            <form action="{{ route('alerts.close', $alert->id) }}" method="POST">
                                @csrf 
                                @method('PUT')
                                <button type="submit" class="dropdown-item text-red">
                                    <i class="fa-solid fa-xmark"></i> Fermer
                                </button>
                            </form>
                            @endif

                        </div>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center; padding: 30px; color: var(--text-muted);">
                    Aucune alerte active. Système nominal.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- TABLEAU 2 : HISTORIQUE DES ALERTES RÉSOLUES --}}
<div class="panel" style="margin-top: 24px;">
    <div class="panel-header">
        <p>Historique des Alertes Résolues</p>
    </div>
    <table class="server-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Ressource</th>
                <th>Source</th>
                <th>Description</th>
                <th>Date de résolution</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resolvedAlerts as $alert)
            <tr style="opacity: 0.8;">
                <td><strong>{{ $alert->code ?? 'ALR-'.$alert->id }}</strong></td>
                <td>{{ $alert->title }}</td>
                <td><span class="badge">{{ strtoupper($alert->source) }}</span></td>
                <td>{{ $alert->description }}</td>
                <td>{{ $alert->updated_at->format('d/m/Y H:i') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center; padding: 20px; color: var(--text-muted);">
                    Aucune alerte résolue récente.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
    // 1. Demi-cercle (Statuts)
    const statusCtx = document.getElementById('statusHalfCircleChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: @json(array_keys($statusData)),
                datasets: [{
                    data: @json(array_values($statusData)),
                    backgroundColor: ['#c0392b', '#e08e3e', '#56825E'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                cutout: '70%', circumference: 180, rotation: 270,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }

    // 2. Évolution (avec filtre)
    const evolutionCtx = document.getElementById('evolutionChart');
    let evolutionChart;
    if (evolutionCtx) {
        evolutionChart = new Chart(evolutionCtx, {
            type: 'bar',
            data: {
                labels: @json($evolutionLabels),
                datasets: [
                    { label: 'Sécurité', data: @json($evolutionSecu), backgroundColor: '#1d4a40' },
                    { label: 'Infra', data: @json($evolutionInfra), backgroundColor: '#e08e3e' }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' } } }
        });
    }

    function filterChartCategory(cat) {
        if (!evolutionChart) return;
        if (cat === 'all') {
            evolutionChart.data.datasets[0].hidden = false;
            evolutionChart.data.datasets[1].hidden = false;
        } else if (cat === 'secu') {
            evolutionChart.data.datasets[0].hidden = false;
            evolutionChart.data.datasets[1].hidden = true;
        } else if (cat === 'infra') {
            evolutionChart.data.datasets[0].hidden = true;
            evolutionChart.data.datasets[1].hidden = false;
        }
        evolutionChart.update();
    }

    // 3. Diagramme en bandes (Sources majeures)
    const majorCtx = document.getElementById('majorSourcesChart');
    if (majorCtx) {
        new Chart(majorCtx, {
            type: 'bar',
            data: {
                labels: @json($majorSourcesLabels),
                datasets: [{
                    label: 'Alertes Majeures',
                    data: @json($majorSourcesData),
                    backgroundColor: '#c0392b'
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });
    }

    function toggleCustomDates() {
        const select = document.getElementById('periodSelect');
        const customDates = document.getElementById('customDates');
        const filterBtn = document.getElementById('filterBtn');
        
        if (select.value === 'custom') {
            customDates.style.display = 'flex';
            filterBtn.style.display = 'block';
        } else {
            customDates.style.display = 'none';
            filterBtn.style.display = 'none';
            document.getElementById('periodForm').submit();
        }
    }
</script>

@endsection