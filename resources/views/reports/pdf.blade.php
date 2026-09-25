<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; color: #333; margin: 40px; }
        h1 { color: #1d4a40; font-size: 24px; text-align: center; margin-bottom: 5px; }
        h2 { color: #1d4a40; font-size: 16px; border-bottom: 2px solid #1d4a40; padding-bottom: 5px; margin-top: 30px; }
        .header-info { text-align: center; font-size: 12px; color: #666; margin-bottom: 30px; }
        .kpi-row { width: 100%; margin-bottom: 20px; text-align: center; }
        .kpi-box { display: inline-block; width: 22%; background: #f8f8f6; border: 1px solid #e3e7e4; border-radius: 4px; padding: 10px; margin: 1%; }
        .kpi-val { font-size: 18px; font-weight: bold; color: #1d4a40; display: block;}
        .kpi-lab { font-size: 10px; color: #8a9490; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 20px; }
        th { background: #1d4a40; color: #fff; padding: 6px; text-align: left; }
        td { border-bottom: 1px solid #eee; padding: 6px; }
        .status-good { color: #56825E; font-weight: bold; }
        .status-warn { color: #e08e3e; font-weight: bold; }
        .status-bad { color: #c0392b; font-weight: bold; }
        .footer { position: fixed; bottom: 20px; left: 40px; right: 40px; text-align: center; font-size: 9px; color: #8a9490; border-top: 1px solid #eee; padding-top: 5px; }
    </style>
</head>
<body>

    <h1>UNIPULSE - Rapport de Monitoring</h1>
    <div class="header-info">
        Généré le {{ $date }} par {{ $author }}
    </div>

    {{-- KPIs --}}
    <div class="kpi-row">
        <div class="kpi-box"><span class="kpi-val">{{ $totalServers }}</span><span class="kpi-lab">Serveurs</span></div>
        <div class="kpi-box"><span class="kpi-val">{{ $totalApps }}</span><span class="kpi-lab">Applications</span></div>
        <div class="kpi-box"><span class="kpi-val">{{ $totalAlerts }}</span><span class="kpi-lab">Alertes Actives</span></div>
        <div class="kpi-box"><span class="kpi-val">{{ $securityScore ?? 'N/A' }}%</span><span class="kpi-lab">Score Sécurité</span></div>
    </div>

    {{-- SECTION 1 : ÉTAT DES SERVEURS --}}
    @if(isset($serverList))
    <h2>I - Server Health Checks</h2>
    <table>
        <thead>
            <tr>
                <th>Nom</th>
                <th>Adresse IP</th>
                <th>OS</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($serverList as $s)
            <tr>
                <td>{{ $s['name'] }}</td>
                <td>{{ $s['ip'] }}</td>
                <td>{{ $s['os'] }}</td>
                <td class="{{ $s['status'] == 'healthy' ? 'status-good' : 'status-bad' }}">{{ ucfirst($s['status']) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- SECTION 2 : ÉTAT DES APPLICATIONS --}}
    @if(isset($appList))
    <h2>II - Application Health Checks</h2>
    <table>
        <thead>
            <tr>
                <th>Nom</th>
                <th>URL</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($appList as $a)
            <tr>
                <td>{{ $a['name'] }}</td>
                <td>{{ $a['url'] }}</td>
                <td class="{{ $a['status'] == 'active' ? 'status-good' : 'status-warn' }}">{{ ucfirst($a['status']) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- SECTION 3 : CERTIFICATS SSL --}}
    @if(isset($sslList))
    <h2>III - SSL Certificate Expiration</h2>
    <table>
        <thead>
            <tr>
                <th>Application</th>
                <th>Jours restants</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sslList as $ssl)
            <tr>
                <td>{{ $ssl['name'] }}</td>
                <td>{{ $ssl['days'] }}</td>
                <td class="{{ is_numeric($ssl['days']) && $ssl['days'] > 30 ? 'status-good' : 'status-warn' }}">
                    {{ is_numeric($ssl['days']) && $ssl['days'] > 30 ? 'VALID' : 'WARNING' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="footer">
        &copy; {{ date('Y') }} UNIPULSE - Rapport automatique confidentiel.
    </div>

</body>
</html>