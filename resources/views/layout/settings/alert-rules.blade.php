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
    <h1>Règles d'alertes</h1>
    <p>Définir les conditions de déclenchement des alertes (MF-183).</p>
</div>



<div class="panel">
    <form id="settingsForm" method="POST" action="{{ route('settings.update') }}">
    @csrf
    
        <h2 style="margin-bottom: 20px;">Seuils de Surveillance</h2>
        
        <div class="settings-form-grid">
            <!-- Section Serveurs -->
            <div class="settings-section">
                <h3>Ressources Serveurs</h3>
                
                <div class="form-group">
                    <label>CPU - Niveau Warning (%)</label>
                    <input type="number" name="cpu_warning" value="{{ old('cpu_warning', \App\Models\Setting::get('cpu_warning', 70)) }}" class="form-control">
                </div>
                
                <div class="form-group">
                    <label>CPU - Niveau Critical (%)</label>
                    <input type="number" name="cpu_critical" value="{{ old('cpu_critical', \App\Models\Setting::get('cpu_critical', 90)) }}" class="form-control">
                </div>

                <div class="form-group">
                    <label>RAM - Niveau Warning (%)</label>
                    <input type="number" name="ram_warning" value="{{ old('ram_warning', \App\Models\Setting::get('ram_warning', 60)) }}" class="form-control">
                </div>

                <div class="form-group">
                    <label>RAM - Niveau Critical (%)</label>
                    <input type="number" name="ram_critical" value="{{ old('ram_critical', \App\Models\Setting::get('ram_critical', 85)) }}" class="form-control">
                </div>
            </div>

            <!-- Section Applications -->
            <div class="settings-section">
                <h3>Ressources Applications</h3>
                
                <div class="form-group">
                    <label>Temps de réponse - Warning (ms)</label>
                    <input type="number" name="rt_warning" value="{{ old('rt_warning', \App\Models\Setting::get('rt_warning', 1000)) }}" class="form-control">
                </div>

                <div class="form-group">
                    <label>Temps de réponse - Critical (ms)</label>
                    <input type="number" name="rt_critical" value="{{ old('rt_critical', \App\Models\Setting::get('rt_critical', 3000)) }}" class="form-control">
                </div>

                <div class="form-group">
                    <label>Disponibilité - Alerte si inférieur à (%)</label>
                    <input type="number" name="avail_critical" value="{{ old('avail_critical', \App\Models\Setting::get('avail_critical', 95)) }}" class="form-control">
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
    const form = document.getElementById('settingsForm');
    const saveBtn = document.getElementById('saveSettingsBtn');
    if (form && saveBtn) {
        form.addEventListener('input', function() {
            saveBtn.disabled = false;
            saveBtn.classList.add('btn-active-state');
        });
    }
</script>

@endsection