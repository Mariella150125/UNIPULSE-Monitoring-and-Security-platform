@extends('layout.app')

@section('content')

<div class="page-title">
    <h1>Modifier le connecteur</h1>
    <p>{{ $connector->name }}</p>
</div>

<form
    action="{{ route('connectors.update', $connector) }}"
    method="POST"
>

    @csrf
    @method('PUT')

    <div class="form-grid">

        <div class="form-group">
            <label for="type">Type</label>
            <select name="type" id="type" required>
                <option value="prometheus" {{ $connector->type === 'prometheus' ? 'selected' : '' }}>Prometheus</option>
                <option value="wazuh" {{ $connector->type === 'wazuh' ? 'selected' : '' }}>Wazuh</option>
            </select>
            @error('type')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="name">Nom</label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name', $connector->name) }}"
                placeholder="Ex : Wazuh — Production"
                required
            >
            @error('name')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group" style="grid-column: span 2;">
            <label for="base_url">URL de base</label>
            <input
                type="url"
                id="base_url"
                name="base_url"
                value="{{ old('base_url', $connector->base_url) }}"
                placeholder="Ex : https://wazuh.example.com"
                required
            >
            @error('base_url')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="api_port">Port API</label>
            <input
                type="number"
                id="api_port"
                name="api_port"
                value="{{ old('api_port', $connector->api_port) }}"
                placeholder="Ex : 55000"
                min="1"
                max="65535"
            >
            <span class="form-hint">Laisser vide pour utiliser le port de l'URL.</span>
            @error('api_port')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="auth_username">Identifiant</label>
            <input
                type="text"
                id="auth_username"
                name="auth_username"
                value="{{ old('auth_username', $connector->auth_username) }}"
                placeholder="Optionnel"
            >
            @error('auth_username')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="auth_password">Mot de passe</label>
            <input
                type="password"
                id="auth_password"
                name="auth_password"
                placeholder="Laisser vide pour ne pas modifier"
            >
            @if ($connector->has_password)
                <span class="form-hint">Un mot de passe est déjà configuré. Laisser vide pour le conserver.</span>
            @endif
            @error('auth_password')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

    </div>

    <div class="form-actions" style="margin-top:24px;">
        <a href="{{ route('connectors.show', $connector) }}" class="btn-cancel">Annuler</a>
        <button type="submit" class="btn-save">
            Enregistrer les modifications
        </button>
    </div>

</form>

@endsection