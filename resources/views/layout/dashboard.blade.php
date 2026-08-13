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
                <p class="kpi-label">Apps supervisées</p><p class="kpi-value">15</p>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon c-sage"><i class="fa-solid fa-satellite-dish"></i></div>
                <p class="kpi-label">Serveurs supervisés</p><p class="kpi-value">30</p>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon c-teal"><i class="fa-solid fa-users"></i></div>
                <p class="kpi-label">Utilisateurs</p><p class="kpi-value">18</p>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon c-red"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <p class="kpi-label">Alertes critiques</p><p class="kpi-value">4</p>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon c-orange"><i class="fa-solid fa-circle-exclamation"></i></div>
                <p class="kpi-label">Alertes majeures</p><p class="kpi-value">9</p>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon c-muted"><i class="fa-solid fa-circle-info"></i></div>
                <p class="kpi-label">Agents Actifs</p><p class="kpi-value">14</p>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon c-orange"><i class="fa-solid fa-folder-open"></i></div>
                <p class="kpi-label">Incidents ouverts</p><p class="kpi-value">6</p>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon c-sage"><i class="fa-solid fa-circle-check"></i></div>
                <p class="kpi-label">Incidents résolus</p><p class="kpi-value">21</p>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon c-teal"><i class="fa-solid fa-shield-halved"></i></div>
                <p class="kpi-label">Score sécurité</p><p class="kpi-value">92%</p>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon c-sage"><i class="fa-solid fa-clipboard-check"></i></div>
                <p class="kpi-label">Score conformité</p><p class="kpi-value">87%</p>
            </div>
        </div>

        {{-- Graphiques conformes au SRS + derniers événements --}}
        <div class="grid-3">
            <div class="panel">

                <div class="panel-header">
                    <p>Évolution des alertes</p>
                    <button class="period-btn">7 derniers jours <i class="fa-solid fa-chevron-down"></i></button>
                </div>
                 <div class="alerChart">
                    <canvas id="alertChart"></canvas>
                 </div>
            </div>

            <div class="panel">
                <div class="panel-header">
                    <p>Évolution du score de sécurité</p>
                    <button class="period-btn">7 derniers jours <i class="fa-solid fa-chevron-down"></i></button>
                </div>
                <div class="securityChart">
                    <canvas id="securityChart"></canvas>
                </div>
                
            </div>

            <div class="panel">
                <div class="panel-header">
                    <p>Derniers événements</p>
                    <a href="#" class="link-see-all">Voir tous</a>
                </div>
                <ul class="event-list">
                    <li><span>Mariella a créé une application</span><time>14:32</time></li>
                    <li><span>Jean a désactivé un serveur</span><time>13:58</time></li>
                    <li><span>Système a détecté une alerte critique</span><time>13:20</time></li>
                    <li><span>Etienne a modifié un rôle</span><time>12:47</time></li>
                </ul>
            </div>
        </div>

        {{-- Santé des serveurs / Etat des agents / Actions rapides --}}
        <div class="grid-3">
            <div class="panel">
                <div class="panel-header">
                    <p>Santé des serveurs</p>
                    <a href="#" class="link-see-all">Voir tous</a>
                </div>
                <table>
                    <thead>
                        <tr><th>Serveur</th><th>Statut</th><th>CPU</th><th>RAM</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>ubuntu-01</td><td><span class="status-dot online"></span>En ligne</td><td>35%</td><td>61%</td></tr>
                        <tr><td>windows-02</td><td><span class="status-dot warning"></span>Avertissement</td><td>82%</td><td>74%</td></tr>
                        <tr><td>api-production</td><td><span class="status-dot critical"></span>Critique</td><td>95%</td><td>90%</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="panel">
                <div class="panel-header"><p>Alertes critiques récentes</p></div>
                <ul class="alert-list">
                    <li>
                        <i class="fa-solid fa-triangle-exclamation c-red"></i>
                        <div><strong>CPU élevé</strong><span>Serveur : ubuntu-01</span></div>
                        <span class="badge badge-critical">Critique</span>
                    </li>
                    <li>
                        <i class="fa-solid fa-circle-exclamation c-orange"></i>
                        <div><strong>SSL expire bientôt</strong><span>Application : API-Paiement</span></div>
                        <span class="badge badge-major">Élevée</span>
                    </li>
                </ul>
            </div>

            <div class="panel">
                <div class="panel-header"><p>Actions rapides</p></div>
                <div class="quick-actions">
                    <button><i class="fa-solid fa-plus"></i>Nouvelle application</button>
                    <button><i class="fa-solid fa-server"></i>Nouveau serveur</button>
                    <button><i class="fa-solid fa-user-plus"></i>Nouvel utilisateur</button>
                    <button><i class="fa-solid fa-key"></i>Générer clé API</button>
                </div>
            </div>
        </div>

        <p class="sync-time">Dernière synchronisation : il y a 2 min</p>
@endsection