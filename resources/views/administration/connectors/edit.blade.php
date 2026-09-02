@extends('layout.app')

@section('content')

<div class="main-content">
<div class="dashboard-content">

<div class="page-title">
    <h1>Modifier le connecteur</h1>
    <p>{{ $connector->name }}</p>
</div>

<form
    action="{{ route('connectors.update', $connector) }}"
    method="POST"
    class="entity-details"
>

    @csrf
    @method('PUT')

    <div class="entity-details-body">

        <div class="modal-grid-2">

            <div class="input-group">
                <label for="type" class="detail-label">Type</label>
                <select name="type" id="type" required>
                    <option value="prometheus" {{ $connector->type === 'prometheus' ? 'selected' : '' }}>Prometheus</option>
                    <option value="wazuh" {{ $connector->type === 'wazuh' ? 'selected' : '' }}>Wazuh</option>
                </select>
                @error('type')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="input-group">
                <label for="name" class="detail-label">Nom</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $connector->name) }}"
                    placeholder="Ex : Wazuh — Production"
                    required
                >
                @error('name')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="input-group" style="grid-column: span 2;">
                <label for="base_url" class="detail-label">URL de base</label>
                <input
                    type="url"
                    id="base_url"
                    name="base_url"
                    value="{{ old('base_url', $connector->base_url) }}"
                    placeholder="Ex : https://wazuh.example.com"
                    required
                >
                @error('base_url')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="input-group">
                <label for="api_port" class="detail-label">Port API</label>
                <input
                    type="number"
                    id="api_port"
                    name="api_port"
                    value="{{ old('api_port', $connector->api_port) }}"
                    placeholder="Ex : 55000"
                    min="1"
                    max="65535"
                >
                <span class="field-error" style="color:var(--text-muted);">Laisser vide pour utiliser le port de l'URL.</span>
                @error('api_port')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="input-group">
                <label for="auth_username" class="detail-label">Identifiant</label>
                <input
                    type="text"
                    id="auth_username"
                    name="auth_username"
                    value="{{ old('auth_username', $connector->auth_username) }}"
                    placeholder="Optionnel"
                >
                @error('auth_username')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="input-group">
                <label for="auth_password" class="detail-label">Mot de passe</label>
                <input
                    type="password"
                    id="auth_password"
                    name="auth_password"
                    placeholder="Laisser vide pour ne pas modifier"
                >
                @if ($connector->has_password)
                    <span class="field-error" style="color:var(--text-muted);">Un mot de passe est déjà configuré. Laisser vide pour le conserver.</span>
                @endif
                @error('auth_password')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

        </div>

    </div>

    <div class="form-actions">
        <a href="{{ route('connectors.show', $connector) }}" class="btn btn-cancel">Annuler</a>
        <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
    </div>

</form>

</div>
</div>

@endsection