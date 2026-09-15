<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .header { background: #1d4a40; color: #fff; padding: 20px; text-align: center; }
        h1 { margin: 0; font-size: 24px; }
        .content { padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #f8f8f6; }
        .kpi-box { display: inline-block; width: 45%; background: #f8f8f6; padding: 15px; margin: 10px; border-radius: 8px; text-align: center; }
        .kpi-value { font-size: 24px; font-weight: bold; color: #1d4a40; }
        .danger { color: #c0392b; font-weight: bold; }
        .success { color: #56825E; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Généré le {{ $date }} par {{ $author }}</p>
    </div>
    <div class="content">
        <h2>1. Indicateurs Clés (KPIs)</h2>
        <div>
            <div class="kpi-box">
                <p>Serveurs Totaux</p>
                <p class="kpi-value">{{ $totalServers }}</p>
            </div>
            <div class="kpi-box">
                <p>Serveurs Critiques</p>
                <p class="kpi-value danger">{{ $criticalServers }}</p>
            </div>
            <div class="kpi-box">
                <p>Applications Actives</p>
                <p class="kpi-value">{{ $activeApps }} / {{ $totalApps }}</p>
            </div>
            <div class="kpi-box">
                <p>Score de Sécurité</p>
                <p class="kpi-value success">{{ $securityScore }}/100</p>
            </div>
        </div>

        <h2>2. Gestion des Alertes</h2>
        <table>
            <tr>
                <th>Indicateur</th>
                <th>Valeur</th>
                <th>Statut</th>
            </tr>
            <tr>
                <td>Total des Alertes</td>
                <td>{{ $totalAlerts }}</td>
                <td>—</td>
            </tr>
            <tr>
                <td>Alertes Résolues</td>
                <td>{{ $resolvedAlerts }}</td>
                <td class="success">Résolu</td>
            </tr>
            <tr>
                <td>Taux de Résolution</td>
                <td>{{ $resolutionRate }} %</td>
                <td @if($resolutionRate < 80) class="danger" @else class="success" @endif>
                    @if($resolutionRate < 80) Faible @else Bon @endif
                </td>
            </tr>
        </table>
    </div>
</body>
</html>