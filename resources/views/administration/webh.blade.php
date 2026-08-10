@extends('layout.app')
@section('Webhook')
@section('content')

        <div class="page-title">
            <h1>Gestion API REST & Webhooks</h1>
        </div>
        <button class= "usr-btn">
            <i class="fa-solid fa-plus"></i>
                 Ajouter un Webhook
        </button>

        {{-- Rangée de KPIs --}}
        <div class="usr-kpi-row">
            <div class="kpi-card">
                <div class="kpi-icon c-teal"><i class="fa-solid fa-code"></i></div>
                <p class="kpi-label">Total API Endpoints</p><p class="kpi-value">12</p>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon c-sage"><i class="fa-solid fa-circle-check"></i></div>
                <p class="kpi-label">API Actives</p><p class="kpi-value">10</p>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon c-teal"><i class="fa-solid fa-satellite-dish"></i></div>
                <p class="kpi-label">Webhooks configurés</p><p class="kpi-value">8</p>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon c-sage"><i class="fa-solid fa-bug"></i></div>
                <p class="kpi-label">Erreurs 24h</p><p class="kpi-value">24</p>
            </div>
        </div>

        {{-- Tableaux API REST + Webhooks (utilise grid-2 existant) --}}
        <div class="grid-2">
                <div class="panel">
                    <div class="panel-header">
                        <div class="search-bar">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" placeholder="Rechercher une API...">
                        </div>
                        <div class="search-filter">
                            <select class="filter-btn">
                                <option>Toutes les méthodes</option>
                                <option>GET</option>
                                <option>POST</option>
                                <option>PUT</option>
                            </select>
                            <select class="filter-btn">
                                <option>Tous les statuts</option>
                                <option>Actif</option>
                                <option>Warning</option>
                                <option>Inactif</option>
                            </select>
                        </div>
                    </div>

                    <table class="server-table">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Méthode</th>
                                <th>Statut</th>
                                <th>Dernier appel</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Agent Collector API</td>
                                <td>GET</td>
                                <td><span class="status-dot online"></span>Actif</td>
                                <td>Il y a 2 min</td>
                                <td>
                                    <button class="icon-btn"><i class="fa-solid fa-eye"></i></button>
                                    <button class="icon-btn"><i class="fa-solid fa-pen"></i></button>
                                    <button class="icon-btn"><i class="fa-solid fa-ellipsis"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>Auth Service API</td>
                                <td>POST</td>
                                <td><span class="status-dot online"></span>Actif</td>
                                <td>Il y a 5 min</td>
                                <td>
                                    <button class="icon-btn"><i class="fa-solid fa-eye"></i></button>
                                    <button class="icon-btn"><i class="fa-solid fa-pen"></i></button>
                                    <button class="icon-btn"><i class="fa-solid fa-ellipsis"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>Monitoring API</td>
                                <td>GET</td>
                                <td><span class="status-dot warning"></span>Warning</td>
                                <td>Il y a 15 min</td>
                                <td>
                                    <button class="icon-btn"><i class="fa-solid fa-eye"></i></button>
                                    <button class="icon-btn"><i class="fa-solid fa-pen"></i></button>
                                    <button class="icon-btn"><i class="fa-solid fa-ellipsis"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>Data Export API</td>
                                <td>POST</td>
                                <td><span class="status-dot online"></span>Actif</td>
                                <td>Il y a 1h</td>
                                <td>
                                    <button class="icon-btn"><i class="fa-solid fa-eye"></i></button>
                                    <button class="icon-btn"><i class="fa-solid fa-pen"></i></button>
                                    <button class="icon-btn"><i class="fa-solid fa-ellipsis"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>Notification API</td>
                                <td>GET</td>
                                <td><span class="status-dot" style="background:var(--text-muted);"></span>Inactif</td>
                                <td>Il y a 24h</td>
                                <td>
                                    <button class="icon-btn"><i class="fa-solid fa-eye"></i></button>
                                    <button class="icon-btn"><i class="fa-solid fa-pen"></i></button>
                                    <button class="icon-btn"><i class="fa-solid fa-ellipsis"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="panel">
                    <div class="panel-header">
                        <div class="search-bar">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" placeholder="Rechercher un webhook...">
                        </div>
                        <div class="search-filter">
                            <select class="filter-btn">
                                <option>Tous les événements</option>
                                <option>alert.created</option>
                                <option>user.login</option>
                                <option>system.update</option>
                            </select>
                            <select class="filter-btn">
                                <option>Tous les statuts</option>
                                <option>Actif</option>
                                <option>Inactif</option>
                            </select>
                        </div>
                    </div>

                    <table class="server-table">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>URL Cible</th>
                                <th>Événement</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Slack Alert</td>
                                <td>https://hooks.slack.com/services/...</td>
                                <td>alert.created</td>
                                <td><span class="status-dot online"></span>Actif</td>
                                <td>
                                    <button class="icon-btn"><i class="fa-solid fa-pen"></i></button>
                                    <button class="icon-btn"><i class="fa-solid fa-trash"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>Email Notification</td>
                                <td>https://api.company.com/notify/...</td>
                                <td>user.login</td>
                                <td><span class="status-dot online"></span>Actif</td>
                                <td>
                                    <button class="icon-btn"><i class="fa-solid fa-pen"></i></button>
                                    <button class="icon-btn"><i class="fa-solid fa-trash"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>Dashboard Update</td>
                                <td>https://dashboard.company.com/...</td>
                                <td>system.update</td>
                                <td><span class="status-dot" style="background:var(--text-muted);"></span>Inactif</td>
                                <td>
                                    <button class="icon-btn"><i class="fa-solid fa-pen"></i></button>
                                    <button class="icon-btn"><i class="fa-solid fa-trash"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>Incident Trigger</td>
                                <td>https://alerts.company.com/inc...</td>
                                <td>incident.triggered</td>
                                <td><span class="status-dot online"></span>Actif</td>
                                <td>
                                    <button class="icon-btn"><i class="fa-solid fa-pen"></i></button>
                                    <button class="icon-btn"><i class="fa-solid fa-trash"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>Report Generator</td>
                                <td>https://reports.company.com/gen...</td>
                                <td>report.generated</td>
                                <td><span class="status-dot online"></span>Actif</td>
                                <td>
                                    <button class="icon-btn"><i class="fa-solid fa-pen"></i></button>
                                    <button class="icon-btn"><i class="fa-solid fa-trash"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
        </div>

        {{-- Section basse (utilise grid-3 et panel existants) --}}
        <div class="grid-3">
            <div class="panel">
                <div class="panel-header">
                    <p>Exemple de requête</p>
                    <button class="period-btn" id="copyCurl"><i class="fa-regular fa-copy"></i> Copier</button>
                </div>
                <div class="api-code-block">
                    <code><span class="api-cmd">curl</span> <span class="api-flag">-X GET</span> \<br>
                    &nbsp;&nbsp;<span class="api-url">"https://api.monitor.com/v1/agents"</span> \<br>
                    &nbsp;&nbsp;<span class="api-flag">-H</span> <span class="api-str">"Authorization: Bearer sk_live_..."</span> \<br>
                    &nbsp;&nbsp;<span class="api-flag">-H</span> <span class="api-str">"Content-Type: application/json"</span>
                    </code>
                </div>
            </div>

            <div class="panel">
                <div class="panel-header">
                    <p>Sécurité API</p>
                </div>
                <div class="api-sec-metrics">
                    <div class="api-sec-row">
                        <div>
                            <p class="api-sec-label">Clés API actives</p>
                            <p class="api-sec-value">12</p>
                        </div>
                        <div class="kpi-icon c-teal" style="width:30px;height:30px;font-size:12px;"><i class="fa-solid fa-key"></i></div>
                    </div>
                    <div class="api-sec-row">
                        <div style="flex:1;">
                            <p class="api-sec-label">Taux d'authentification</p>
                            <p class="api-sec-value">99.2%</p>
                            <div class="api-progress"><div class="api-progress-bar" style="width:99.2%;background:var(--sage-green);"></div></div>
                        </div>
                    </div>
                    <div class="api-sec-row">
                        <div>
                            <p class="api-sec-label">Requêtes bloquées (24h)</p>
                            <p class="api-sec-value" style="color:var(--red);">45</p>
                        </div>
                        <div class="kpi-icon c-red" style="width:30px;height:30px;font-size:12px;"><i class="fa-solid fa-shield-halved"></i></div>
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="panel-header">
                    <p>Événements classifiés</p>
                </div>
                <div class="api-event-metrics">
                    <div class="api-event-row">
                        <span class="api-event-dot" style="background:var(--red);"></span>
                        <div class="api-event-info">
                            <p class="api-event-name">Alertes Critiques</p>
                            <div class="api-event-bar"><div class="api-event-bar-fill" style="width:5%;background:var(--red);"></div></div>
                        </div>
                        <span class="api-event-count">8</span>
                    </div>
                    <div class="api-event-row">
                        <span class="api-event-dot" style="background:var(--orange);"></span>
                        <div class="api-event-info">
                            <p class="api-event-name">Alertes Majeures</p>
                            <div class="api-event-bar"><div class="api-event-bar-fill" style="width:10%;background:var(--orange);"></div></div>
                        </div>
                        <span class="api-event-count">15</span>
                    </div>
                    <div class="api-event-row">
                        <span class="api-event-dot" style="background:var(--sage-green);"></span>
                        <div class="api-event-info">
                            <p class="api-event-name">Alertes Mineures</p>
                            <div class="api-event-bar"><div class="api-event-bar-fill" style="width:28%;background:var(--sage-green);"></div></div>
                        </div>
                        <span class="api-event-count">42</span>
                    </div>
                    <div class="api-event-row">
                        <span class="api-event-dot" style="background:var(--text-muted);"></span>
                        <div class="api-event-info">
                            <p class="api-event-name">Informations</p>
                            <div class="api-event-bar"><div class="api-event-bar-fill" style="width:80%;background:var(--text-muted);"></div></div>
                        </div>
                        <span class="api-event-count">120</span>
                    </div>
                </div>
            </div>
        </div>

        <p class="sync-time">Dernière synchronisation : il y a 2 min</p>
@endsection