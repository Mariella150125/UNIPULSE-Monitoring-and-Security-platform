@extends('layout.app')

@section('content')
@if(session('success'))
    <div class="success-message">
        <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger" style="background: rgba(192, 57, 43, 0.1); border: 1px solid var(--red); color: var(--red); padding: 12px 18px; border-radius: 8px; margin-bottom: 20px;">
        <i class="fa-solid fa-triangle-exclamation"></i> {{ session('error') }}
    </div>
@endif
<div class="page-title">
    <a href="{{ route('settings') }}" class="btn btn-cancel">
        <i class="fa-solid fa-arrow-left"></i> Retour aux paramètres
    </a>
    <h1>Paramètres des Connecteurs</h1>
    <p>Configurer le comportement global de l'intégration Wazuh et Prometheus.</p>
</div>


<div class="panel">
    <form id="settingsForm" method="POST" action="{{ route('settings.update') }}">
        @csrf
        
        <h2 style="margin-bottom: 20px;">Configuration de la synchronisation</h2>
        
        <div class="settings-form-grid">
            <!-- Section Wazuh -->
            <div class="settings-section">
                <h3>Paramètres Wazuh</h3>
                
                <div class="form-group">
                    <label>Timeout de l'API (secondes)</label>
                    <input type="number" name="wazuh_timeout" value="{{ old('wazuh_timeout', \App\Models\Setting::get('wazuh_timeout', 5)) }}" class="form-control">
                </div>

                <div class="form-group">
                    <label>Fréquence de synchronisation (minutes)</label>
                    <input type="number" name="wazuh_sync_freq" value="{{ old('wazuh_sync_freq', \App\Models\Setting::get('wazuh_sync_freq', 15)) }}" class="form-control">
                </div>

                <div class="form-group checkbox-group">
                    <label>
                        <input type="checkbox" name="wazuh_active" value="1" {{ (\App\Models\Setting::get('wazuh_active', '1') == '1') ? 'checked' : '' }}>
                        Activer l'intégration Wazuh
                    </label>
                </div>
            </div>

            <!-- Section Prometheus -->
            <div class="settings-section">
                <h3>Paramètres Prometheus</h3>
                
                <div class="form-group">
                    <label>Timeout de l'API (secondes)</label>
                    <input type="number" name="prom_timeout" value="{{ old('prom_timeout', \App\Models\Setting::get('prom_timeout', 5)) }}" class="form-control">
                </div>

                <div class="form-group">
                    <label>Fréquence de collecte des métriques (secondes)</label>
                    <input type="number" name="prom_scrape_freq" value="{{ old('prom_scrape_freq', \App\Models\Setting::get('prom_scrape_freq', 30)) }}" class="form-control">
                </div>

                <div class="form-group checkbox-group">
                    <label>
                        <input type="checkbox" name="prom_active" value="1" {{ (\App\Models\Setting::get('prom_active', '1') == '1') ? 'checked' : '' }}>
                        Activer l'intégration Prometheus
                    </label>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('settings') }}" class="btn btn-cancel">
                <i class="fa-solid fa-xmark"></i> Annuler
            </a>
            <button type="submit" id="saveSettingsBtn" class="usr-btn" disabled>
                <i class="fa-solid fa-floppy-disk"></i> Enregistrer les modifications
            </button>
        </div>
    </form>
</div>

<script>
    // Script pour activer le bouton Enregistrer
    const form = document.getElementById('settingsForm');
    const saveBtn = document.getElementById('saveSettingsBtn');
    if (form && saveBtn) {
        form.addEventListener('input', function() {
            saveBtn.disabled = false;
        });
        form.addEventListener('change', function() {
            saveBtn.disabled = false;
        });
    }
</script>

@endsection