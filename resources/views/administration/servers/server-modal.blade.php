

<x-modal id="server-modal" title="Ajouter un serveur">
    
    <form action="{{ route('server.store') }}" method="POST" id="server-form">
        @csrf

        <p class="modal-section-title">Informations générales</p>
        <div class="modal-grid-2">
            <div class="input-group">
                <label>Nom *</label>
                <input type="text" name="name" required>
            </div>
            <div class="input-group">
                <label>Nom d'hôte *</label>
                <input type="text" name="hostname" required>
            </div>
            <div class="input-group">
                <label>Adresse IP *</label>
                <input type="text" name="ip_address" required>
            </div>
            <div class="input-group">
                <label>Port</label>
                <input type="number" name="port" value="22">
            </div>
            <div class="input-group">
                <label>Système d'exploitation *</label>
                <select name="os" required>
                    <option value="">Sélectionner</option>
                    <option value="linux">Linux</option>
                    <option value="windows">Windows</option>
                </select>
            </div>
            <div class="input-group">
                <label>Version OS</label>
                <input type="text" name="os_version" placeholder="ex. Ubuntu 22.04">
            </div>
            <div class="input-group">
                <label>Environnement *</label>
                <select name="environment" required>
                    <option value="production">Production</option>
                    <option value="preprod">Préproduction</option>
                    <option value="test">Test</option>
                    <option value="dev">Développement</option>
                </select>
            </div>
            <div class="input-group">
                <label>Criticité *</label>
                <select name="criticality" required>
                    <option value="high">Élevée</option>
                    <option value="medium">Moyenne</option>
                    <option value="low">Faible</option>
                </select>
            </div>
            <div class="input-group">
                <label>Département</label>
                <input type="text" name="department">
            </div>
            <div class="input-group">
                <label>Groupe de serveurs</label>
                <select name="group_id">
                    <option value="">Aucun</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="input-group">
            <label>Description</label>
            <input type="text" name="description">
        </div>
        <div class="input-group">
            <label>Tags</label>
            <input type="text" name="tags" placeholder="séparés par des virgules">
        </div>

        <p class="modal-section-title">Connecteur Prometheus</p>
        <div class="modal-grid-2">
            <div class="input-group">
                <label>Instance</label>
                <input type="text" name="prometheus_instance" placeholder="ex. srv-web-04:9100">
            </div>
            <div class="input-group">
                <label>Job</label>
                <input type="text" name="prometheus_job" placeholder="ex. node_exporter">
            </div>
        </div>

        <p class="modal-section-title">Connecteur Wazuh</p>
        <div class="modal-grid-2">
            <div class="input-group">
                <label>Agent ID</label>
                <input type="text" name="wazuh_agent_id">
            </div>
            <div class="input-group">
                <label>Groupe Wazuh</label>
                <input type="text" name="wazuh_group">
            </div>
        </div>
    </form>

    <x-slot:footer>
        <button type="button" class="btn-secondary" data-modal-close="server-modal">Annuler</button>
        <button type="submit" form="server-form" class="login-btn">Enregistrer</button>
    </x-slot:footer>

</x-modal>