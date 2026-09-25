@extends('layout.app')

@section('content')

@if ($errors->any())
    <div class="flash-message error">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif
<div class="main-content">
<div class="dashboard-content">

<div class="page-title">
    <h1>Modifier le serveur</h1>
    <p>{{ $server->hostname }}</p>
</div>

<form
    action="{{ route('server.update', $server) }}"
    method="POST"
    class="entity-details"
>

    @csrf
    @method('PUT')

    <div class="entity-details-body">

        <h3 style="margin-top: 20px;">Informations Générales</h3>
        <div class="modal-grid-2">

            <div class="input-group">
                <label for="name" class="detail-label">Nom</label>
                <input type="text" id="name" name="name" value="{{ old('name', $server->name) }}" required>
                @error('name') <span class="field-error">{{ $message }}</span> @enderror
            </div>

            <div class="input-group">
                <label for="hostname" class="detail-label">Hostname</label>
                <input type="text" id="hostname" name="hostname" value="{{ old('hostname', $server->hostname) }}" required>
                @error('hostname') <span class="field-error">{{ $message }}</span> @enderror
            </div>

            <div class="input-group">
                <label for="ip_address" class="detail-label">Adresse IP</label>
                <input type="text" id="ip_address" name="ip_address" value="{{ old('ip_address', $server->ip_address) }}" required>
                @error('ip_address') <span class="field-error">{{ $message }}</span> @enderror
            </div>

            <div class="input-group">
                <label for="port" class="detail-label">Port</label>
                <input type="number" id="port" name="port" value="{{ old('port', $server->port) }}" placeholder="Ex : 22" min="1" max="65535">
                @error('port') <span class="field-error">{{ $message }}</span> @enderror
            </div>

            <div class="input-group">
                <label for="os" class="detail-label">Système d'exploitation</label>
                <input type="text" id="os" name="os" value="{{ old('os', $server->os) }}" required placeholder="Ex : Ubuntu, Windows, Debian">
                @error('os') <span class="field-error">{{ $message }}</span> @enderror
            </div>

            <div class="input-group">
                <label for="os_version" class="detail-label">Version de l'OS</label>
                <input type="text" id="os_version" name="os_version" value="{{ old('os_version', $server->os_version) }}" placeholder="Ex : 22.04">
                @error('os_version') <span class="field-error">{{ $message }}</span> @enderror
            </div>

            <div class="input-group">
                <label for="environment" class="detail-label">Environnement</label>
                <input type="text" id="environment" name="environment" value="{{ old('environment', $server->environment) }}" required placeholder="Ex : production, staging, development">
                @error('environment') <span class="field-error">{{ $message }}</span> @enderror
            </div>

            <div class="input-group">
                <label for="department" class="detail-label">Département</label>
                <input type="text" id="department" name="department" value="{{ old('department', $server->department) }}" placeholder="Ex : Infrastructure">
                @error('department') <span class="field-error">{{ $message }}</span> @enderror
            </div>

            <div class="input-group" style="grid-column: span 2;">
                <label for="description" class="detail-label">Description</label>
                <textarea id="description" name="description" rows="3" placeholder="Optionnel" style="width:100%;border:1px solid #c7cec9;border-radius:10px;background:#fff;padding:10px 16px;font-size:14px;font-family:inherit;color:var(--text-dark);outline:none;resize:vertical;">{{ old('description', $server->description) }}</textarea>
                @error('description') <span class="field-error">{{ $message }}</span> @enderror
            </div>

            <div class="input-group" style="grid-column: span 2;">
                <label for="tags" class="detail-label">Tags</label>
                <input type="text" id="tags" name="tags" value="{{ old('tags', $server->tags ? implode(', ', $server->tags) : '') }}" placeholder="tag1, tag2, tag3">
                <span class="field-error" style="color:var(--text-muted);">Séparés par des virgules</span>
                @error('tags') <span class="field-error">{{ $message }}</span> @enderror
            </div>

            <div class="input-group">
                <label for="group_id" class="detail-label">Groupe</label>
                <select id="group_id" name="group_id">
                    <option value="">— Choisir un groupe —</option>
                    @foreach ($groups as $group)
                        <option value="{{ $group->id }}" {{ old('group_id', $server->group_id) == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                    @endforeach
                </select>
                @error('group_id') <span class="field-error">{{ $message }}</span> @enderror
            </div>
        </div>

        <h3 style="margin-top: 30px;">Connecteurs de Monitoring</h3>
        <div class="modal-grid-2">
            
            {{-- NOUVEAU CHAMP : Instance Prometheus --}}
            <div class="input-group">
                <label for="prometheus_instance" class="detail-label">Instance Prometheus</label>
                <input type="text" id="prometheus_instance" name="prometheus_instance" value="{{ old('prometheus_instance', $server->prometheus_instance) }}" placeholder="ex: 159.198.66.125:9100">
                @error('prometheus_instance') <span class="field-error">{{ $message }}</span> @enderror
            </div>

            {{-- NOUVEAU CHAMP : Job Prometheus --}}
            <div class="input-group">
                <label for="prometheus_job" class="detail-label">Job Prometheus</label>
                <input type="text" id="prometheus_job" name="prometheus_job" value="{{ old('prometheus_job', $server->prometheus_job) }}" placeholder="ex: node_exporter">
                @error('prometheus_job') <span class="field-error">{{ $message }}</span> @enderror
            </div>

            {{-- NOUVEAU CHAMP : Agent ID Wazuh --}}
            <div class="input-group">
                <label for="wazuh_agent_id" class="detail-label">Agent ID Wazuh</label>
                <input type="text" id="wazuh_agent_id" name="wazuh_agent_id" value="{{ old('wazuh_agent_id', $server->wazuh_agent_id) }}" placeholder="ex: 001">
                @error('wazuh_agent_id') <span class="field-error">{{ $message }}</span> @enderror
            </div>

            {{-- NOUVEAU CHAMP : Groupe Wazuh --}}
            <div class="input-group">
                <label for="wazuh_group" class="detail-label">Groupe Wazuh</label>
                <input type="text" id="wazuh_group" name="wazuh_group" value="{{ old('wazuh_group', $server->wazuh_group) }}" placeholder="ex: default">
                @error('wazuh_group') <span class="field-error">{{ $message }}</span> @enderror
            </div>

        </div>

    </div>

    <div class="form-actions">
        <a href="{{ route('server.show', $server) }}" class="btn btn-cancel">Annuler</a>
        <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
    </div>

</form>

</div>
</div>

@endsection