<x-modal 
    id="application-modal" 
    title="Ajouter une application"
>
    <form  
        action="{{ route('appli.store') }}" 
        method="POST" 
        id="application-form"
    >
        @csrf

        {{-- ============================= --}}
        {{-- INFORMATIONS GÉNÉRALES --}}
        {{-- ============================= --}}

        <p class="modal-section-title">
            Informations générales
        </p>

        <div class="modal-grid-2">

            <div class="input-group">
                <label for="name">Nom *</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    required
                >
            </div>

            <div class="input-group">
                <label for="application_type_id">
                    Type d'application *
                </label>

                <select
                    id="application_type_id"
                    name="application_type_id"
                    required
                >
                    <option value="">Sélectionner</option>

                    @foreach($applicationTypes as $type)
                        <option value="{{ $type->id }}">
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="input-group">
                <label for="language">Langage</label>
                <input
                    type="text"
                    id="language"
                    name="language"
                    placeholder="PHP, Python, Java..."
                >
            </div>

            <div class="input-group">
                <label for="framework">Framework</label>
                <input
                    type="text"
                    id="framework"
                    name="framework"
                    placeholder="Laravel, Django, Spring..."
                >
            </div>

            <div class="input-group">
                <label for="version">Version</label>
                <input
                    type="text"
                    id="version"
                    name="version"
                    placeholder="Ex : 2.4.1"
                >
            </div>

            <div class="input-group">
                <label for="environment">Environnement *</label>

                <select
                    id="environment"
                    name="environment"
                    required
                >
                    <option value="">Sélectionner</option>
                    <option value="development">Développement</option>
                    <option value="test">Test</option>
                    <option value="staging">Préproduction</option>
                    <option value="production">Production</option>
                </select>
            </div>

            <div class="input-group input-full">
                <label for="url">URL</label>
                <input
                    type="url"
                    id="url"
                    name="url"
                    placeholder="https://exemple.com"
                >
            </div>

            <div class="input-group input-full">
                <label for="description">Description</label>
                <textarea
                    id="description"
                    name="description"
                    rows="3"
                    placeholder="Description de l'application..."
                ></textarea>
            </div>

        </div>


        
        {{-- ============================= --}}
{{-- HÉBERGEMENT --}}
{{-- ============================= --}}

    <p class="modal-section-title">
        Hébergement
    </p>

    <div class="modal-grid-2">

        {{-- Application hébergée ou non --}}
        <div class="input-group input-full">
            <label for="is_hosted">
                Application hébergée ? *
            </label>

            <select
                id="is_hosted"
                name="is_hosted"
                required
            >
                <option value="">Sélectionner</option>
                <option value="1">Oui, elle est hébergée</option>
                <option value="0">Non, elle n'est pas hébergée</option>
            </select>
        </div>

        {{-- Serveur --}}
        <div
            class="input-group hosted-field"
            id="server-field"
        >
            <label for="server_id">
                Serveur principal *
            </label>

            <select
                id="server_id"
                name="server_id"
            >
                <option value="">Sélectionner un serveur</option>

                @foreach($servers as $server)
                    <option value="{{ $server->id }}">
                        {{ $server->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Port --}}
        <div
            class="input-group hosted-field"
            id="port-field"
        >
            <label for="port">
                Port
            </label>

            <input
                type="number"
                id="port"
                name="port"
                placeholder="Ex : 80, 443, 8080"
            >
        </div>

        {{-- Répertoire --}}
        <div
            class="input-group input-full hosted-field"
            id="deployment-path-field"
        >
            <label for="deployment_path">
                Répertoire de déploiement
            </label>

            <input
                type="text"
                id="deployment_path"
                name="deployment_path"
                placeholder="/var/www/application"
            >
        </div>
        <div class="input-group">
            <label for="status">
                Statut *
            </label>

            <select
                id="status"
                name="status"
                required
            >
                <option value="planned" selected>
                    Planifiée
                </option>

                <option value="development">
                    En développement
                </option>

                <option value="testing">
                    En test
                </option>

                <option value="staging">
                    Préproduction
                </option>

                <option value="active">
                    Active
                </option>

                <option value="maintenance">
                    En maintenance
                </option>

                <option value="suspended">
                    Suspendue
                </option>

                <option value="retired">
                    Retirée
                </option>
            </select>
        </div>

        </div>
            

        {{-- ============================= --}}
        {{-- MONITORING --}}
        {{-- ============================= --}}

        <p class="modal-section-title">
            Monitoring
        </p>

        <div class="modal-grid-2">

            <div class="input-group">
                <label for="monitoring_enabled">
                    Monitoring
                </label>

                <select
                    id="monitoring_enabled"
                    name="monitoring_enabled"
                >
                    <option value="1">Activé</option>
                    <option value="0">Désactivé</option>
                </select>
            </div>

            <div class="input-group">
                <label for="prometheus_job">
                    Job Prometheus
                </label>

                <input
                    type="text"
                    id="prometheus_job"
                    name="prometheus_job"
                    placeholder="Ex : application_api"
                >
            </div>

            <div class="input-group">
                <label for="metrics_endpoint">
                    Endpoint des métriques
                </label>

                <input
                    type="text"
                    id="metrics_endpoint"
                    name="metrics_endpoint"
                    placeholder="/metrics"
                >
            </div>

            <div class="input-group">
                <label for="scrape_interval">
                    Intervalle de collecte
                </label>

                <input
                    type="text"
                    id="scrape_interval"
                    name="scrape_interval"
                    placeholder="Ex : 15s"
                >
            </div>

        </div>


        {{-- ============================= --}}
        {{-- SÉCURITÉ --}}
        {{-- ============================= --}}

        <p class="modal-section-title">
            Sécurité
        </p>

        <div class="modal-grid-2">

            <div class="input-group">
                <label for="wazuh_enabled">
                    Surveillance Wazuh
                </label>

                <select
                    id="wazuh_enabled"
                    name="wazuh_enabled"
                >
                    <option value="1">Activée</option>
                    <option value="0">Désactivée</option>
                </select>
            </div>

            <div class="input-group">
                <label for="criticality">
                    Criticité
                </label>

                <select
                    id="criticality"
                    name="criticality"
                >
                    <option value="">Sélectionner</option>
                    <option value="low">Faible</option>
                    <option value="medium">Moyenne</option>
                    <option value="high">Élevée</option>
                    <option value="critical">Critique</option>
                </select>
            </div>

        </div>

    </form>

    <x-slot:footer>

        <button
            type="button"
            class="btn btn-cancel"
            data-modal-close="application-modal"
        >
            Annuler
        </button>

        <button
            type="submit"
            form="application-form"
            class="btn btn-primary"
        >
            Enregistrer
        </button>

    </x-slot:footer>

</x-modal>