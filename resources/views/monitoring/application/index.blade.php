@extends('layout.app')

@section('content')

<div class="page-title">
    <h1>Monitoring Applicatif</h1>
    <p>Vue d'ensemble des performances et de la disponibilité .</p>
</div>
<div style="margin-bottom: 24px; display: flex; justify-content: flex-end;">
    <form method="GET" action="{{ route('monitoring.application.index') }}">
        <select name="group_id" class="filter-btn" style="padding: 10px 15px; width: 250px;" onchange="this.form.submit()">
            <option value="">Tous les groupes</option>
            @foreach($applicationGroups as $group)
                <option value="{{ $group->id }}" {{ request('group_id') == $group->id ? 'selected' : '' }}>
                    {{ $group->name }}
                </option>
            @endforeach
        </select>
    </form>
</div>


{{-- LIGNE 1 : Stat Panels (KPIs) --}}
<div class="usr-kpi-row">
    <div class="kpi-card">
        <div class="kpi-icon c-teal"><i class="fa-solid fa-window-restore"></i></div>
        <p class="kpi-label">Total Applications</p>
        <p class="kpi-value">{{ $totalApps }}</p>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon c-sage"><i class="fa-solid fa-circle-check"></i></div>
        <p class="kpi-label">Disponibles</p>
        <p class="kpi-value">{{ $availableApps }}</p>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon c-red"><i class="fa-solid fa-circle-xmark"></i></div>
        <p class="kpi-label">Indisponibles</p>
        <p class="kpi-value">{{ $unavailableApps }}</p>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon c-orange"><i class="fa-solid fa-percent"></i></div>
        <p class="kpi-label">Disponibilité Actuelle</p>
        <p class="kpi-value">{{ $availabilityPercent }}%</p>
    </div>
</div>

{{-- LIGNE 2 : Graphiques de Tendance --}}
<div class="grid-2">
    <div class="panel">
        <div class="panel-header"><p>Portfolio Availability Trend</p></div>
        <div class="alertChart" style="height: 200px;">
            <canvas id="availTrendChart"></canvas>
        </div>
    </div>
    <div class="panel">
        <div class="panel-header"><p>Response Time Trend</p></div>
        <div class="alertChart" style="height: 200px;">
            <canvas id="respTrendChart"></canvas>
        </div>
    </div>
</div>

{{-- LIGNE 3 : Tableaux denses avec barres de progression --}}
<div class="grid-2">
    
    {{-- Tableau 1 : Disponibilité Système (24h) --}}
    <div class="panel">
        <div class="panel-header">
            <p>System Availability — Rolling 24 Hours</p>
        </div>
        <table class="server-table">
            <thead>
                <tr>
                    <th>Application</th>
                    <th>Groupe</th>
                    <th style="width: 40%;">Disponibilité (24h)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($appStats as $stat)
                <tr>
                    <td>
                        <a href="{{ route('monitoring.application.show', $stat['id']) }}" style="text-decoration: none; color: var(--text-dark); font-weight: 500;">
                            {{ $stat['name'] }}
                        </a>
                    </td>
                    <td><span class="env-badge">{{ $stat['group'] }}</span></td>
                    <td>
                        @php
                            $availColor = $stat['availability'] >= 99 ? 'var(--sage-green)' : ($stat['availability'] >= 80 ? 'var(--orange)' : 'var(--red)');
                        @endphp
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="flex: 1; height: 8px; background: var(--page-bg); border-radius: 4px; overflow: hidden;">
                                <div style="width: {{ $stat['availability'] }}%; height: 100%; background: {{ $availColor }};"></div>
                            </div>
                            <span style="font-size: 12px; font-weight: 600; color: {{ $availColor }}; width: 50px; text-align: right;">{{ $stat['availability'] }}%</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Tableau 2 : Temps de réponse soutenu (10 min) --}}
    <div class="panel">
        <div class="panel-header">
            <p>Sustained Response Time — 10-Minute Average</p>
        </div>
        <table class="server-table">
            <thead>
                <tr>
                    <th>Application</th>
                    <th>Statut</th>
                    <th style="width: 40%;">Temps de réponse</th>
                </tr>
            </thead>
            <tbody>
                @foreach($appStats as $stat)
                @php
                    $respMs = $stat['response_time'];
                    $respDisplay = $respMs === '—' ? '—' : $respMs . ' ms';
                    // On calcule une largeur de barre relative (max 2000ms pour l'affichage)
                    $barWidth = $respMs === '—' ? 0 : min(100, ($respMs / 2000) * 100);
                    $respColor = $respMs === '—' ? 'var(--text-muted)' : ($respMs < 500 ? 'var(--sage-green)' : ($respMs < 1000 ? 'var(--orange)' : 'var(--red)'));
                @endphp
                <tr>
                    <td>
                        <a href="{{ route('monitoring.application.show', $stat['id']) }}" style="text-decoration: none; color: var(--text-dark); font-weight: 500;">
                            {{ $stat['name'] }}
                        </a>
                    </td>
                    <td>
                        <span class="status-dot {{ $stat['status'] === 'active' ? 'online' : 'offline' }}"></span>
                        {{ ucfirst($stat['status']) }}
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="flex: 1; height: 8px; background: var(--page-bg); border-radius: 4px; overflow: hidden;">
                                <div style="width: {{ $barWidth }}%; height: 100%; background: {{ $respColor }};"></div>
                            </div>
                            <span style="font-size: 12px; font-weight: 600; color: {{ $respColor }}; width: 60px; text-align: right;">{{ $respDisplay }}</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
{{-- LIGNE 4 : SSL Certificate Days Remaining --}}
<div class="panel" style="margin-top: 24px;">
    <div class="panel-header">
        <p>SSL Certificate Days Remaining</p>
    </div>
    <table class="server-table">
        <thead>
            <tr>
                <th>Application</th>
                <th style="width: 50%;">Jours restants avant expiration</th>
            </tr>
        </thead>
        <tbody>
                        @forelse($sslStats as $ssl)
            <tr>
                <td><strong>{{ $ssl['name'] }}</strong></td>
                <td>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        @php
                            // On vérifie si 'days' est un vrai chiffre
                            $isNumeric = is_numeric($ssl['days']);
                            $barWidth = $isNumeric ? min(100, ($ssl['days'] / 90) * 100) : 0;
                        @endphp
                        
                        <div style="flex: 1; height: 8px; background: var(--page-bg); border-radius: 4px; overflow: hidden;">
                            @if($isNumeric)
                                <div style="width: {{ $barWidth }}%; height: 100%; background: {{ $ssl['color'] }}; transition: width 0.5s ease;"></div>
                            @else
                                <div style="width: 100%; height: 100%; background: repeating-linear-gradient(45deg, var(--border-color), var(--border-color) 5px, transparent 5px, transparent 10px);"></div>
                            @endif
                        </div>
                        
                        <span style="font-size: 12px; font-weight: 600; color: {{ $ssl['color'] }}; width: 50px; text-align: right;">{{ $ssl['days'] }} @if($isNumeric) j @endif</span>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="2" style="text-align: center; padding: 20px; color: var(--text-muted);">
                    Aucune application avec une URL HTTPS configurée.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<script>
    window.availTrendLabels = @json($trendLabels);
    window.availTrendData = @json($availTrend);
    window.respTrendLabels = @json($trendLabels);
    window.respTrendData = @json($respTrend);
</script>
@endsection