@extends('layout.app')

@section('content')

<div class="page-title">
    <a href="{{ route('monitoring.servers.index') }}" class="btn btn-cancel">
        <i class="fa-solid fa-arrow-left"></i> Retour à la liste
    </a>
    <h1>Monitoring : {{ $server->name }}</h1>
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
        </div>
    </div>

    <!-- Carte de Monitoring -->
    <div class="panel">
        <div class="panel-header">
            <p>Métriques en temps réel</p>
        </div>
        
        <div class="monitor-card" data-server-id="{{ $server->id }}">
            <div class="monitor-header">
                <h3>État actuel</h3>
                <span class="monitor-status" id="server-status">Chargement...</span>
            </div>

            <div class="metric-grid">
                <div class="metric-box">
                    <span class="metric-label">CPU</span>
                    <strong class="metric-value" id="cpu-value">-- %</strong>
                </div>
                <div class="metric-box">
                    <span class="metric-label">RAM</span>
                    <strong class="metric-value" id="memory-value">-- %</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- APPLICATIONS HÉBERGÉES -->
<div class="panel" style="margin-top: 24px;">
    <div class="panel-header">
        <p>Applications hébergées ({{ $server->applications->count() }})</p>
    </div>

    @if($server->applications->isNotEmpty())
        <div class="table-container">
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
                            <td>
                                <strong>{{ $app->name }}</strong>
                            </td>
                            <td>
                                <span class="env-badge env-{{ $app->environment ?? 'dev' }}">
                                    {{ ucfirst($app->environment ?? 'N/A') }}
                                </span>
                            </td>
                            <td>
                                <span class="status-dot online"></span> Actif
                            </td>
                            <td>
                                <a href="{{ route('appli.show', $app->id) }}" class="usr-btn-1">
                                    <i class="fa-solid fa-eye"></i> Voir l'application
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div style="text-align: center; padding: 30px; color: var(--text-muted);">
            <i class="fa-solid fa-info-circle" style="font-size: 24px; margin-bottom: 10px;"></i><br>
            Aucune application hébergée sur ce serveur.
        </div>
    @endif
</div>

<!-- SCRIPT POUR LE MONITORING EN TEMPS RÉEL -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const monitorCard = document.querySelector('.monitor-card');
        if (!monitorCard) return;

        const serverId = monitorCard.dataset.serverId;
        const statusEl = document.getElementById('server-status');
        const cpuEl = document.getElementById('cpu-value');
        const memEl = document.getElementById('memory-value');

        async function loadMetrics() {
            try {
                const response = await fetch(`/monitoring/servers/${serverId}/metrics`);
                if (!response.ok) return;
                
                const data = await response.json();
                if (!data.success) return;

                if (data.status === 'online') {
                    statusEl.textContent = '🟢 En ligne';
                    statusEl.style.color = 'var(--sage-green)';
                } else {
                    statusEl.textContent = '🔴 Hors ligne';
                    statusEl.style.color = 'var(--red)';
                }

                cpuEl.textContent = data.cpu !== null ? `${data.cpu.toFixed(1)} %` : '-- %';
                memEl.textContent = data.memory !== null ? `${data.memory.toFixed(1)} %` : '-- %';

            } catch (error) {
                console.error('Erreur monitoring:', error);
                statusEl.textContent = 'Erreur réseau';
                statusEl.style.color = 'var(--red)';
            }
        }

        loadMetrics();
        setInterval(loadMetrics, 15000);
    });
</script>

@endsection