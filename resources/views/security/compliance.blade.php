@extends('layout.app')

@section('content')

<div class="page-title">
    <h1>Centre de Sécurité & Conformité</h1>
    <p>Tableau de bord visuel des scores de sécurité.</p>
</div>

<div class="usr-kpi-row">
    <div class="kpi-card">
        <div class="kpi-icon c-teal"><i class="fa-solid fa-shield-halved"></i></div>
        <p class="kpi-label">Score Global Sécurité</p>
        <p class="kpi-value">{{ $globalSecurityScore }}/100</p>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon c-sage"><i class="fa-solid fa-window-restore"></i></div>
        <p class="kpi-label">Score Global Applications</p>
        <p class="kpi-value">{{ $globalAppScore }}/100</p>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon c-orange"><i class="fa-solid fa-server"></i></div>
        <p class="kpi-label">Score Global Serveurs</p>
        <p class="kpi-value">{{ $globalServerScore }}/100</p>
    </div>
</div>

<div class="grid-2">
    {{-- GRAPHIQUE POSTURE SÉCURITÉ --}}
    <div class="panel">
        <div class="panel-header"><p>Posture de Sécurité (Top 5)</p></div>
        <div class="alertChart" style="height: 300px; position: relative;">
            <canvas id="securityChart"></canvas>
        </div>
    </div>

    {{-- TOP 5 APPLICATIONS VULNÉRABLES --}}
    <div class="panel">
        <div class="panel-header"><p>Top 5 Applications vulnérables</p></div>
        <table class="server-table">
            <thead><tr><th>Application</th><th>Score</th><th>Évaluation</th></tr></thead>
            <tbody>
                @forelse($topVulnerableApps as $app)
                <tr>
                    <td><strong>{{ $app['name'] }}</strong></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="flex: 1; height: 8px; background: var(--page-bg); border-radius: 4px; overflow: hidden;">
                                <div style="width: {{ $app['score'] }}%; height: 100%; background: {{ $app['color'] }};"></div>
                            </div>
                            <span style="font-weight: 600; color: {{ $app['color'] }}; width: 40px; text-align: right;">{{ $app['score'] }}%</span>
                        </div>
                    </td>
                    <td><span class="badge" style="background: {{ $app['color'] }}; color: white;">{{ $app['level'] }}</span></td>
                </tr>
                @empty
                <tr><td colspan="3" style="text-align:center; color: var(--text-muted);">Aucune application.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- OWASP TOP 10 AVEC CORRÉLATION --}}
<div class="panel" style="margin-top: 24px;">
    <div class="panel-header"><p>Correspondance OWASP Top 10 (Guidelines externes)</p></div>
    <table class="server-table">
        <thead><tr><th>Catégorie</th><th>Description</th><th>Statut</th><th>Guideline</th></tr></thead>
         <tbody>
            {{-- Passage de @foreach à @forelse pour gérer le cas vide --}}
            @forelse($owaspCategories as $cat)
            <tr>
                <td><strong>{{ $cat['code'] }}</strong></td>
                <td>{{ $cat['name'] }}</td>
                <td><span class="status-dot online"></span> {{ $cat['status'] }}</td>
                <td><a href="{{ $cat['guideline'] }}" target="_blank" class="usr-btn-1" style="padding: 5px 10px; font-size: 12px;">Voir Guideline</a></td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align:center; padding: 20px; color: var(--text-muted);">
                    Aucune catégorie OWASP active pour le moment.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
    window.securityLabels = @json($chartLabels);
    window.securityData = @json($chartData);
    window.securityColors = @json($chartColors);
</script>

@endsection