@extends('layout.app')

@section('content')

<div class="page-title">
    <a href="{{ route('reports.index') }}" class="btn btn-cancel">
        <i class="fa-solid fa-arrow-left"></i> Retour aux rapports
    </a>
    <div style="display:flex; justify-content:space-between; align-items:center; width: 100%; padding-left: 20px; flex-wrap: wrap; gap: 15px;">
        <div>
            <h1>Statistiques Globales</h1>
            <p>Vue d'ensemble - {{ $periodText }} (MF-178).</p>
        </div>
        
        <div style="display: flex; gap: 15px; align-items: center;">
            <!-- SÉLECTEUR DE PÉRIODE -->
            <form action="{{ route('reports.statistics') }}" method="GET" id="periodForm" style="display: flex; gap: 10px; align-items: center;">
                <select name="period" id="periodSelect" onchange="toggleCustomDates()" class="form-control" style="width: auto;">
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

           <!-- BOUTON EXPORTER INTÉGRÉ ICI -->
            <div style="position: relative;">
                <button class="btn btn-primary btn-export-trigger" onclick="toggleExportMenu(event)">
                    <i class="fa-solid fa-download"></i> Exporter ▾
                </button>
                <div id="exportDropdownMenu" style="display: none; position: absolute; right: 0; top: 110%; background: #fff; border: 1px solid var(--border-color); border-radius: 8px; padding: 5px; min-width: 200px; z-index: 1000; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                    
                    <!-- 1. IMAGE (Capture d'écran JS) -->
                    <a href="#" onclick="exportAsImage('statsContent'); return false;" style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; color: var(--text-dark); text-decoration: none; border-radius: 6px;">
                        <i class="fa-solid fa-image" style="color: var(--orange);"></i> Exporter en Image (PNG)
                    </a>
                    
                    <!-- 2. PDF (Vrai fichier DomPDF) -->
                    <a href="{{ route('reports.generate-pdf') }}" style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; color: var(--text-dark); text-decoration: none; border-radius: 6px;">
                        <i class="fa-solid fa-file-pdf" style="color: var(--red);"></i> Exporter en PDF
                    </a>

                    <!-- 3. EXCEL (Vrai fichier Maatwebsite) -->
                    <a href="{{ route('reports.generate-excel') }}" style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; color: var(--text-dark); text-decoration: none; border-radius: 6px;">
                        <i class="fa-solid fa-file-excel" style="color: var(--sage-green);"></i> Exporter en Excel
                    </a>

                    <!-- 4. WORD (Vrai fichier PhpWord) -->
                    <a href="{{ route('reports.generate-word') }}" style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; color: var(--text-dark); text-decoration: none; border-radius: 6px;">
                        <i class="fa-solid fa-file-word" style="color: var(--dark-teal);"></i> Exporter en Word
                    </a>

                </div>
            </div>
        </div>
    </div>
</div>



<div id="statsContent" style="margin-top: 15px;">
    
    {{-- BLOC 1 : KPIs RESSOURCES & MÉTRIQUES --}}
    <div class="usr-kpi-row">
        <div class="kpi-card">
            <div class="kpi-icon c-teal"><i class="fa-solid fa-server"></i></div>
            <p class="kpi-label">Total Serveurs</p>
            <p class="kpi-value">{{ $totalServers }}</p>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon c-red"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <p class="kpi-label">Serveurs Critiques</p>
            <p class="kpi-value">{{ $criticalServers }}</p>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon c-sage"><i class="fa-solid fa-window-restore"></i></div>
            <p class="kpi-label">Applications Actives</p>
            <p class="kpi-value">{{ $activeApps }} / {{ $totalApps }}</p>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon c-orange"><i class="fa-solid fa-gauge-high"></i></div>
            <p class="kpi-label">Temps Réponse Moy.</p>
            <p class="kpi-value">{{ $avgResponseTime }} <span style="font-size: 14px; color: var(--text-muted);">ms</span></p>
        </div>
    </div>

    {{-- BLOC 2 : ÉVOLUTION & SANTÉ --}}
    <div class="grid-2" style="margin-bottom: 24px;">
        <div class="panel">
            <div class="panel-header"><p>Évolution des Alertes ({{ $periodText }})</p></div>
            <div style="padding: 20px; height: 300px; position: relative;">
                <canvas id="evolutionChart" style="max-width: 100%; height: 100% !important;"></canvas>
            </div>
        </div>
        <div class="panel">
            <div class="panel-header"><p>Santé des Serveurs (Temps Réel)</p></div>
            <div style="padding: 20px; height: 300px; position: relative;">
                <canvas id="serverHealthChart" style="max-width: 100%; height: 100% !important;"></canvas>
            </div>
        </div>
    </div>

    {{-- BLOC 3 : SÉCURITÉ & CONFORMITÉ --}}
    <div class="grid-2">
        <div class="panel">
            <div class="panel-header"><p>Top 5 Applications Vulnérables</p></div>
            <div style="padding: 20px; height: 300px; position: relative;">
                <canvas id="vulnAppsChart" style="max-width: 100%; height: 100% !important;"></canvas>
            </div>
        </div>
        <div class="panel">
            <div class="panel-header"><p>Conformité & Résolution</p></div>
            <div style="padding: 20px;">
                <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 8px;">Score Global de Sécurité ({{ $securityScore }}/100)</p>
                <div style="width: 100%; height: 12px; background: var(--page-bg); border-radius: 6px; margin-bottom: 20px; overflow: hidden;">
                    <div style="width: {{ $securityScore }}%; height: 100%; background: var(--sage-green); border-radius: 6px;"></div>
                </div>
                
                <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 8px;">Conformité OWASP Top 10 ({{ $owaspCompliance }}%)</p>
                <div style="width: 100%; height: 12px; background: var(--page-bg); border-radius: 6px; margin-bottom: 20px; overflow: hidden;">
                    <div style="width: {{ $owaspCompliance }}%; height: 100%; background: var(--dark-teal); border-radius: 6px;"></div>
                </div>

                <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 8px;">Taux de Résolution des Alertes ({{ $resolutionRate }}%)</p>
                <div style="width: 100%; height: 12px; background: var(--page-bg); border-radius: 6px; overflow: hidden;">
                    <div style="width: {{ $resolutionRate }}%; height: 100%; background: var(--orange); border-radius: 6px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- LIBRAIRIES JS POUR L'EXPORT --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
    // 1. Gestion des dates
    function toggleCustomDates() {
        const select = document.getElementById('periodSelect');
        const customDates = document.getElementById('customDates');
        const filterBtn = document.getElementById('filterBtn');
        if (select.value === 'custom') {
            customDates.style.display = 'flex'; filterBtn.style.display = 'block';
        } else {
            customDates.style.display = 'none'; filterBtn.style.display = 'none';
            document.getElementById('periodForm').submit();
        }
    }

    // 2. Graphique Évolution Alertes
    const evolutionCtx = document.getElementById('evolutionChart');
    if (evolutionCtx) {
        new Chart(evolutionCtx, {
            type: 'bar',
            data: { labels: @json($evolutionLabels), datasets: [{ label: 'Alertes', data: @json($evolutionData), backgroundColor: '#1d4a40' }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });
    }

    // 3. Graphique Demi-cercle (Santé Serveurs)
    const serverHealthCtx = document.getElementById('serverHealthChart');
    if (serverHealthCtx) {
        new Chart(serverHealthCtx, {
            type: 'doughnut',
            data: { labels: @json($serverHealthLabels), datasets: [{ data: @json($serverHealthData), backgroundColor: ['#56825E', '#c0392b', '#e08e3e', '#8a9490'], borderWidth: 0 }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '70%', circumference: 180, rotation: 270, plugins: { legend: { position: 'bottom' } } }
        });
    }

    // 4. Graphique Top Apps Vulnérables
    const vulnAppsCtx = document.getElementById('vulnAppsChart');
    if (vulnAppsCtx) {
        new Chart(vulnAppsCtx, {
            type: 'bar',
            data: { labels: @json($topVulnAppsLabels), datasets: [{ label: 'Indice de Vulnérabilité', data: @json($topVulnsAppsData), backgroundColor: '#c0392b' }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    }

    // 5. Menu Exporter
    function toggleExportMenu(event) {
        event.stopPropagation(); 
        const menu = document.getElementById('exportDropdownMenu');
        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    }
    document.addEventListener('click', function(event) {
        const menu = document.getElementById('exportDropdownMenu');
        if (menu && menu.style.display === 'block') {
            if (!menu.contains(event.target) && !event.target.closest('.btn-export-trigger')) {
                menu.style.display = 'none';
            }
        }
    });

    function exportAsImage(elementId) {
        const element = document.getElementById(elementId);
        document.getElementById('exportDropdownMenu').style.display = 'none';
        document.body.style.cursor = 'wait';
        html2canvas(element, { backgroundColor: '#ffffff', scale: 2, useCORS: true }).then(canvas => {
            const link = document.createElement('a');
            link.download = 'statistiques_globales.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
            document.body.style.cursor = 'default';
        });
    }

    function exportAsPDF(elementId) {
         window.location.href = '{{ route("reports.generate-pdf") }}';
    }
</script>
@endsection