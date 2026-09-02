@extends('layout.app')

@section('content')

<div class="main-content">
<div class="dashboard-content">

@if ($errors->any())
    <div class="flash-message error">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

<div class="page-title">
    <h1>Modifier l'application</h1>
    <p>{{ $application->name }}</p>
</div>

<form action="{{ route('appli.update', $application->id) }}" method="POST" class="entity-details">
    @csrf
    @method('PUT')

    <div class="entity-details-body">

        <div class="modal-grid-2">

            <div class="input-group">
                <label for="name" class="detail-label">Nom *</label>
                <input type="text" id="name" name="name" value="{{ old('name', $application->name) }}" required>
                @error('name')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="input-group">
                <label for="application_type_id" class="detail-label">Type d'application *</label>
                <select id="application_type_id" name="application_type_id" required>
                    <option value="">Sélectionner...</option>
                    @foreach($applicationTypes as $type)
                        <option value="{{ $type->id }}" {{ old('application_type_id', $application->application_type_id) == $type->id ? 'selected' : '' }}>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
                @error('application_type_id')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="input-group">
                <label for="environment" class="detail-label">Environnement *</label>
                <select id="environment" name="environment" required>
                    <option value="development" {{ old('environment', $application->environment) === 'development' ? 'selected' : '' }}>Development</option>
                    <option value="test" {{ old('environment', $application->environment) === 'test' ? 'selected' : '' }}>Test</option>
                    <option value="staging" {{ old('environment', $application->environment) === 'staging' ? 'selected' : '' }}>Staging</option>
                    <option value="production" {{ old('environment', $application->environment) === 'production' ? 'selected' : '' }}>Production</option>
                </select>
                @error('environment')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="input-group">
                <label for="url" class="detail-label">URL</label>
                <input type="url" id="url" name="url" value="{{ old('url', $application->url) }}">
                @error('url')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="input-group">
                <label for="language" class="detail-label">Langage</label>
                <input type="text" id="language" name="language" value="{{ old('language', $application->language) }}">
                @error('language')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="input-group">
                <label for="framework" class="detail-label">Framework</label>
                <input type="text" id="framework" name="framework" value="{{ old('framework', $application->framework) }}">
                @error('framework')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="input-group">
                <label for="version" class="detail-label">Version</label>
                <input type="text" id="version" name="version" value="{{ old('version', $application->version) }}">
                @error('version')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="input-group">
                <label for="is_hosted" class="detail-label">Hébergé *</label>
                <select id="is_hosted" name="is_hosted" required>
                    <option value="1" {{ old('is_hosted', $application->is_hosted) ? 'selected' : '' }}>Oui</option>
                    <option value="0" {{ !old('is_hosted', $application->is_hosted) ? 'selected' : '' }}>Non</option>
                </select>
                @error('is_hosted')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="input-group">
                <label for="server_id" class="detail-label">Serveur</label>
                <select id="server_id" name="server_id">
                    <option value="">Aucun</option>
                    @foreach($servers as $server)
                        <option value="{{ $server->id }}" {{ old('server_id', $application->server_id) == $server->id ? 'selected' : '' }}>
                            {{ $server->name }}
                        </option>
                    @endforeach
                </select>
                @error('server_id')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="input-group">
                <label for="port" class="detail-label">Port</label>
                <input type="number" id="port" name="port" value="{{ old('port', $application->port) }}" min="1" max="65535">
                @error('port')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="input-group">
                <label for="criticality" class="detail-label">Criticité</label>
                <select id="criticality" name="criticality">
                    <option value="">Aucune</option>
                    <option value="low" {{ old('criticality', $application->criticality) === 'low' ? 'selected' : '' }}>Basse</option>
                    <option value="medium" {{ old('criticality', $application->criticality) === 'medium' ? 'selected' : '' }}>Moyenne</option>
                    <option value="high" {{ old('criticality', $application->criticality) === 'high' ? 'selected' : '' }}>Haute</option>
                    <option value="critical" {{ old('criticality', $application->criticality) === 'critical' ? 'selected' : '' }}>Critique</option>
                </select>
                @error('criticality')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="input-group" style="grid-column: span 2;">
                <label for="description" class="detail-label">Description</label>
                <textarea
                    id="description"
                    name="description"
                    rows="3"
                    style="width:100%;border:1px solid #c7cec9;border-radius:10px;background:#fff;padding:10px 16px;font-size:14px;font-family:inherit;color:var(--text-dark);outline:none;resize:vertical;"
                >{{ old('description', $application->description) }}</textarea>
                @error('description')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

        </div>

    </div>

    <div class="form-actions">
        <a href="{{ route('appli.index') }}" class="btn btn-cancel">Annuler</a>
        <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
    </div>

</form>

</div>
</div>

@endsection