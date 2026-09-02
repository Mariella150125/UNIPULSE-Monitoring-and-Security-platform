@extends('layout.app')

@section('content')

<div class="page-title">
    <!-- BOUTON RETOUR EN HAUT -->
    <a href="{{ route('settings') }}" class="btn btn-cancel">
        <i class="fa-solid fa-arrow-left"></i> Retour aux paramètres
    </a>
    <h1>Paramètres de la plateforme</h1>
</div>

<!-- MESSAGE DE SUCCÈS LORS DE LA SAUVEGARDE -->
@if(session('success'))
    <div class="success-message">
        <i class="fa-solid fa-circle-check"></i>
        {{ session('success') }}
    </div>
@endif

<div class="panel">
    <!-- Navigation par Onglets -->
    <div class="tabs-container">
        <button class="tab-btn active" data-tab="general">Général</button>
        <button class="tab-btn" data-tab="notifications">Notifications</button>
        <button class="tab-btn" data-tab="security">Sécurité</button>
        <button class="tab-btn" data-tab="logging">Journalisation</button>
    </div>

    <form id="settingsForm" method="POST" action="{{ route('settings.platform.update') }}">
        @csrf @method('PUT')
        
        <!-- CHAMP CACHÉ POUR CONNAÎTRE L'ONGLET ACTIF POUR L'AUDIT -->
        <input type="hidden" name="active_tab" id="active_tab_input" value="general">

        <!-- ONGLET GENERAL -->
        <div class="tab-content active" id="tab-general">
            <h2>Délais de traitement et Seuils d'alerte</h2>
            
            <div class="settings-form-grid">
                @foreach(['Serveur', 'Applications', 'Sécurité'] as $category)
                    <div class="settings-section">
                        <h3>{{ $category }}</h3>
                        <div class="form-group">
                            <label>Délai alerte Critique (min)</label>
                            <input type="number" name="delai_{{ strtolower($category) }}_critique" value="{{ old('delai_'.strtolower($category).'_critique', $settings['delai_'.strtolower($category).'_critique'] ?? 15) }}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Délai alerte Majeure (min)</label>
                            <input type="number" name="delai_{{ strtolower($category) }}_majeure" value="{{ old('delai_'.strtolower($category).'_majeure', $settings['delai_'.strtolower($category).'_majeure'] ?? 60) }}" class="form-control">
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- ONGLET NOTIFICATIONS -->
        <div class="tab-content" id="tab-notifications">
            <h2>Canaux de notifications</h2>
            <div class="settings-section">
                <div class="form-group checkbox-group">
                    <label><input type="checkbox" name="notif_email" value="1" {{ ($settings['notif_email'] ?? false) ? 'checked' : '' }}> Notifications par Email</label>
                </div>
                <div class="form-group checkbox-group">
                    <label><input type="checkbox" name="notif_slack" value="1" {{ ($settings['notif_slack'] ?? false) ? 'checked' : '' }}> Notifications par Slack</label>
                </div>
                <div class="form-group checkbox-group">
                    <label><input type="checkbox" name="notif_sms" value="1" {{ ($settings['notif_sms'] ?? false) ? 'checked' : '' }}> Notifications par SMS</label>
                </div>
            </div>
        </div>

        <!-- ONGLET SECURITE -->
        <div class="tab-content" id="tab-security">
            <h2>Paramètres de sécurité</h2>
            <div class="settings-section">
                <div class="form-group">
                    <label>Expiration de session (minutes)</label>
                    <input type="number" name="session_lifetime" value="{{ $settings['session_lifetime'] ?? 120 }}" class="form-control">
                </div>
            </div>
        </div>

        <!-- ONGLET JOURNALISATION -->
        <div class="tab-content" id="tab-logging">
            <h2>Paramètres de journalisation</h2>
            <div class="settings-section">
                <div class="form-group">
                    <label>Niveau de log minimum</label>
                    <select name="log_level" class="form-control">
                        <option value="error" {{ ($settings['log_level'] ?? '') == 'error' ? 'selected' : '' }}>Erreur</option>
                        <option value="warning" {{ ($settings['log_level'] ?? '') == 'warning' ? 'selected' : '' }}>Warning</option>
                        <option value="info" {{ ($settings['log_level'] ?? '') == 'info' ? 'selected' : '' }}>Info</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- BOUTONS D'ACTION -->
        <div class="form-actions">
            <!-- BOUTON RETOUR EN BAS -->
            <a href="{{ route('settings') }}" class="btn btn-cancel">
                <i class="fa-solid fa-xmark"></i> Annuler
            </a>
            
            <!-- BOUTON ENREGISTRER -->
            <button type="submit" id="saveSettingsBtn" class="usr-btn" disabled>
                <i class="fa-solid fa-floppy-disk"></i> Enregistrer les modifications
            </button>
        </div>
    </form>
</div>



@endsection