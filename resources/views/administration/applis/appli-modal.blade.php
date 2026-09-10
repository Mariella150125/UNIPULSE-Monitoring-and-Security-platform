<x-modal id="application-modal" title="Ajouter une application">
    <form action="{{ route('appli.store') }}" method="POST" id="application-form">
        @csrf

        <!-- Navigation par Onglets -->
        <div class="tabs-container">
            <button type="button" class="tab-btn active" data-tab="general">Général</button>
            <button type="button" class="tab-btn" data-tab="frontend">Frontend</button>
            <button type="button" class="tab-btn" data-tab="backend">Backend</button>
            <button type="button" class="tab-btn" data-tab="db">Base de données</button>
            <button type="button" class="tab-btn" data-tab="hosting">Hébergement & Sécurité</button>
        </div>

        <!-- ONGLET 1 : GÉNÉRAL -->
        <div class="tab-content active" id="tab-general">
            <div class="modal-grid-2">
                <div class="input-group">
                    <label for="name">Nom *</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="input-group">
                    <label for="client_name">Nom du client</label>
                    <input type="text" id="client_name" name="client_name" placeholder="Ex: Société ABC">
                </div>
                <div class="input-group">
                    <label for="application_type_id">Type d'application *</label>
                    <select id="application_type_id" name="application_type_id" required>
                        <option value="">Sélectionner</option>
                        @foreach($applicationTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="input-group">
                    <label for="environment">Environnement *</label>
                    <select id="environment" name="environment" required>
                        <option value="">Sélectionner</option>
                        <option value="development">Développement</option>
                        <option value="test">Test</option>
                        <option value="staging">Préproduction</option>
                        <option value="production">Production</option>
                    </select>
                </div>
                <div class="input-group">
                    <label for="status">Statut *</label>
                    <select id="status" name="status" required>
                        
                        <option value="development">En développement</option>
                        <option value="testing">En test</option>
                        <option value="staging">Préproduction</option>
                        <option value="active">Active</option>
                        
                    </select>
                </div>
                <div class="input-group">
                    <label for="application_group_id">Groupe d'application</label>
                    <select id="application_group_id" name="application_group_id">
                        <option value="">Aucun groupe</option>
                        @foreach($applicationGroups as $group)
                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="input-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="3" placeholder="Description de l'application..."></textarea>
            </div>
        </div>

        <!-- ONGLET 2 : FRONTEND -->
        <div class="tab-content" id="tab-frontend">
            <div class="modal-grid-2">
                <div class="input-group">
                    <label for="frontend_language">Langage Frontend</label>
                    <input type="text" id="frontend_language" name="frontend_language" placeholder="Ex: JavaScript, TypeScript">
                </div>
                <div class="input-group">
                    <label for="frontend_framework">Framework Frontend</label>
                    <input type="text" id="frontend_framework" name="frontend_framework" placeholder="Ex: React, Vue.js, Angular">
                </div>
                <div class="input-group">
                    <label for="frontend_url">URL Frontend</label>
                    <input type="url" id="frontend_url" name="frontend_url" placeholder="https://app.exemple.com">
                </div>
                <div class="input-group">
                    <label for="frontend_version">Version Frontend</label>
                    <input type="text" id="frontend_version" name="frontend_version" placeholder="Ex: 1.2.4">
                </div>
            </div>
        </div>

        <!-- ONGLET 3 : BACKEND -->
        <div class="tab-content" id="tab-backend">
            <div class="modal-grid-2">
                <div class="input-group">
                    <label for="language">Langage Backend *</label>
                    <input type="text" id="language" name="language" placeholder="Ex: PHP, Python, Java">
                </div>
                <div class="input-group">
                    <label for="framework">Framework Backend *</label>
                    <input type="text" id="framework" name="framework" placeholder="Ex: Laravel, Django, Spring">
                </div>
                <div class="input-group">
                    <label for="url">URL de l'API Backend</label>
                    <input type="url" id="url" name="url" placeholder="https://api.exemple.com">
                </div>
                <div class="input-group">
                    <label for="version">Version Backend</label>
                    <input type="text" id="version" name="version" placeholder="Ex: 10.x">
                </div>
            </div>
        </div>

        <!-- ONGLET 4 : BASE DE DONNÉES -->
        <div class="tab-content" id="tab-db">
            <div class="modal-grid-2">
                <div class="input-group">
                    <label for="database_type">Type de SGBD</label>
                    <select id="database_type" name="database_type">
                        <option value="">Aucune</option>
                        <option value="postgresql">PostgreSQL</option>
                        <option value="mysql">MySQL / MariaDB</option>
                        <option value="mongodb">MongoDB</option>
                        <option value="sqlserver">SQL Server</option>
                        <option value="oracle">Oracle</option>
                    </select>
                </div>
                <div class="input-group">
                    <label for="database_name">Nom de la base</label>
                    <input type="text" id="database_name" name="database_name" placeholder="ex: unipulse_prod">
                </div>
                <div class="input-group">
                    <label for="database_host">Serveur (IP/Hostname)</label>
                    <input type="text" id="database_host" name="database_host" placeholder="ex: 192.168.1.50">
                </div>
                <div class="input-group">
                    <label for="database_port">Port</label>
                    <input type="number" id="database_port" name="database_port" placeholder="ex: 5432">
                </div>
            </div>
        </div>

               <!-- ONGLET 5 : HÉBERGEMENT & SÉCURITÉ -->
        <div class="tab-content" id="tab-hosting">
            
            <p class="modal-section-title">Hébergement</p>
            <div class="modal-grid-2">
                <div class="input-group input-full">
                    <label for="is_hosted">Application hébergée ? *</label>
                    <select id="is_hosted" name="is_hosted" required>
                        <option value="">Sélectionner</option>
                        <option value="1">Oui, elle est hébergée</option>
                        <option value="0">Non, elle n'est pas hébergée</option>
                    </select>
                </div>
                <div class="input-group hosted-field" id="server-field">
                    <label for="server_id">Serveur principal *</label>
                    <select id="server_id" name="server_id">
                        <option value="">Sélectionner un serveur</option>
                        @foreach($servers as $server)
                            <option value="{{ $server->id }}">{{ $server->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="input-group hosted-field" id="port-field">
                    <label for="port">Port d'écoute</label>
                    <input type="number" id="port" name="port" placeholder="Ex : 80, 443, 8080">
                </div>
                <div class="input-group input-full hosted-field" id="deployment-path-field">
                    <label for="deployment_path">Répertoire de déploiement</label>
                    <input type="text" id="deployment_path" name="deployment_path" placeholder="/var/www/application">
                </div>
            </div>

            <p class="modal-section-title">Monitoring Prometheus (MF-1)</p>
            <div class="modal-grid-2">
                <div class="input-group">
                    <label for="monitoring_enabled">Monitoring Activé ?</label>
                    <select id="monitoring_enabled" name="monitoring_enabled">
                        <option value="1">Oui</option>
                        <option value="0">Non</option>
                    </select>
                </div>
                <div class="input-group">
                    <label for="prometheus_job">Job Prometheus</label>
                    <input type="text" id="prometheus_job" name="prometheus_job" placeholder="Ex : application_api">
                </div>
                <div class="input-group">
                    <label for="metrics_endpoint">Endpoint des métriques</label>
                    <input type="text" id="metrics_endpoint" name="metrics_endpoint" placeholder="/metrics">
                </div>
                <div class="input-group">
                    <label for="scrape_interval">Intervalle de collecte</label>
                    <input type="text" id="scrape_interval" name="scrape_interval" placeholder="Ex : 15s">
                </div>
                <div class="input-group input-full">
                    <label for="url_health_check">URL de Health-Check (si pas de Prometheus)</label>
                    <input type="url" id="url_health_check" name="url_health_check" placeholder="https://app.exemple.com/health">
                </div>
            </div>

            <p class="modal-section-title">Sécurité Wazuh</p>
            <div class="modal-grid-2">
                <div class="input-group">
                    <label for="wazuh_enabled">Surveillance Wazuh</label>
                    <select id="wazuh_enabled" name="wazuh_enabled">
                        <option value="1">Activée</option>
                        <option value="0">Désactivé</option>
                    </select>
                </div>
            </div>
            <p class="modal-section-title">Sécurité Wazuh (MF-23, MF-26)</p>
            <div class="modal-grid-2">
                <div class="input-group">
                    <label for="wazuh_enabled">Surveillance Wazuh</label>
                    <select id="wazuh_enabled" name="wazuh_enabled">
                        <option value="1">Activée</option>
                        <option value="0">Désactivé</option>
                    </select>
                </div>
                <div class="input-group">
                    <label for="wazuh_agent_id">Agent ID Wazuh</label>
                    <input type="text" id="wazuh_agent_id" name="wazuh_agent_id" placeholder="Ex: 001">
                </div>
                <div class="input-group">
                    <label for="wazuh_group">Groupe Wazuh</label>
                    <input type="text" id="wazuh_group" name="wazuh_group" placeholder="Ex: webservers">
                </div>
            </div>
        </div>
        
    </form>

    <x-slot:footer>
        <button type="button" class="btn btn-cancel" data-modal-close="application-modal">Annuler</button>
        <button type="submit" form="application-form" class="btn btn-primary">Enregistrer</button>
    </x-slot:footer>
</x-modal>