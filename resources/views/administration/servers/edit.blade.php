@extends('layout.app')

@section('content')

<div class="page-title">
    <h1>Modifier le serveur</h1>
    <p>{{ $server->hostname }}</p>
</div>

<form
    action="{{ route('server.update', $server) }}"
    method="POST"
>

    @csrf
    @method('PUT')

    <div class="form-grid">

        <div class="form-group">
            <label for="name">Nom</label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name', $server->name) }}"
                required
            >
            @error('name')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="hostname">Hostname</label>
            <input
                type="text"
                id="hostname"
                name="hostname"
                value="{{ old('hostname', $server->hostname) }}"
                required
            >
            @error('hostname')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="ip_address">Adresse IP</label>
            <input
                type="text"
                id="ip_address"
                name="ip_address"
                value="{{ old('ip_address', $server->ip_address) }}"
                required
            >
            @error('ip_address')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="port">Port</label>
            <input
                type="number"
                id="port"
                name="port"
                value="{{ old('port', $server->port) }}"
                placeholder="Ex : 22"
                min="1"
                max="65535"
            >
            @error('port')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="os">Système d'exploitation</label>
            <input
                type="text"
                id="os"
                name="os"
                value="{{ old('os', $server->os) }}"
                required
                placeholder="Ex : Ubuntu, Windows, Debian"
            >
            @error('os')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="os_version">Version de l'OS</label>
            <input
                type="text"
                id="os_version"
                name="os_version"
                value="{{ old('os_version', $server->os_version) }}"
                placeholder="Ex : 22.04"
            >
            @error('os_version')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="environment">Environnement</label>
            <input
                type="text"
                id="environment"
                name="environment"
                value="{{ old('environment', $server->environment) }}"
                required
                placeholder="Ex : production, staging, development"
            >
            @error('environment')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="department">Département</label>
            <input
                type="text"
                id="department"
                name="department"
                value="{{ old('department', $server->department) }}"
                placeholder="Ex : Infrastructure"
            >
            @error('department')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group" style="grid-column: span 2;">
            <label for="description">Description</label>
            <textarea
                id="description"
                name="description"
                rows="3"
                placeholder="Optionnel"
            >{{ old('description', $server->description) }}</textarea>
            @error('description')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group" style="grid-column: span 2;">
            <label for="tags">Tags</label>
            <input
                type="text"
                id="tags"
                name="tags"
                value="{{ old('tags', $server->tags ? implode(', ', $server->tags) : '') }}"
                placeholder="tag1, tag2, tag3"
            >
            <span class="form-hint">Séparés par des virgules</span>
            @error('tags')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="group_id">Groupe</label>
            <select
                id="group_id"
                name="group_id"
            >
                <option value="">— Choisir un groupe —</option>
                @foreach ($groups as $group)
                    <option
                        value="{{ $group->id }}"
                        {{ $group->name }}
                    </option>
                @endforeach
            </select>
            @error('group_id')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

    </div>

    <div class="form-actions" style="margin-top:24px;">
        <a href="{{ route('server.show', $server) }}" class="btn-cancel">Annuler</a>
        <button type="submit" class="btn-save">
            Enregistrer les modifications
        </button>
    </div>

</form>

@endsection