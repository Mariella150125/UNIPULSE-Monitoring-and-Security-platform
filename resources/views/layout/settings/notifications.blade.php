@extends('layout.app')

@section('content')

<div class="page-title">
    <a href="{{ route('settings') }}" class="btn btn-cancel">
        <i class="fa-solid fa-arrow-left"></i> Retour aux paramètres
    </a>
    <h1>Canaux de notification</h1>
    <p>Configurer comment les alertes sont remises (MF-184).</p>
</div>

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

<div class="panel">
    <form id="settingsForm" method="POST" action="{{ route('settings.update') }}">
        @csrf
        
        <h2 style="margin-bottom: 20px;">Matrice de notification</h2>
        
        <table class="server-table">
            <thead>
                <tr>
                    <th>Priorité de l'alerte</th>
                    <th style="text-align: center;">Pop-up & Son</th>
                    <th style="text-align: center;">Email</th>
                    <th style="text-align: center;">Slack / Teams</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="badge badge-critical">CRITIQUE</span></td>
                    <td style="text-align: center;"><input type="checkbox" name="critical_popup" value="1" {{ (\App\Models\Setting::get('critical_popup', '1') == '1') ? 'checked' : '' }}></td>
                    <td style="text-align: center;"><input type="checkbox" name="critical_email" value="1" {{ (\App\Models\Setting::get('critical_email', '1') == '1') ? 'checked' : '' }}></td>
                    <td style="text-align: center;"><input type="checkbox" name="critical_slack" value="1" {{ (\App\Models\Setting::get('critical_slack', '0') == '1') ? 'checked' : '' }}></td>
                </tr>
                <tr>
                    <td><span class="badge badge-major">HAUTE</span></td>
                    <td style="text-align: center;"><input type="checkbox" name="high_popup" value="1" {{ (\App\Models\Setting::get('high_popup', '1') == '1') ? 'checked' : '' }}></td>
                    <td style="text-align: center;"><input type="checkbox" name="high_email" value="1" {{ (\App\Models\Setting::get('high_email', '1') == '1') ? 'checked' : '' }}></td>
                    <td style="text-align: center;"><input type="checkbox" name="high_slack" value="1" {{ (\App\Models\Setting::get('high_slack', '0') == '1') ? 'checked' : '' }}></td>
                </tr>
                <tr>
                    <td><span class="badge">MOYENNE</span></td>
                    <td style="text-align: center;"><input type="checkbox" name="med_popup" value="1" {{ (\App\Models\Setting::get('med_popup', '1') == '1') ? 'checked' : '' }}></td>
                    <td style="text-align: center;"><input type="checkbox" name="med_email" value="1" {{ (\App\Models\Setting::get('med_email', '0') == '1') ? 'checked' : '' }}></td>
                    <td style="text-align: center;"><input type="checkbox" name="med_slack" value="1" {{ (\App\Models\Setting::get('med_slack', '0') == '1') ? 'checked' : '' }}></td>
                </tr>
                <tr>
                    <td><span class="badge" style="background: var(--grey);">FAIBLE</span></td>
                    <td style="text-align: center;"><input type="checkbox" name="low_popup" value="1" {{ (\App\Models\Setting::get('low_popup', '0') == '1') ? 'checked' : '' }}></td>
                    <td style="text-align: center;"><input type="checkbox" name="low_email" value="1" {{ (\App\Models\Setting::get('low_email', '0') == '1') ? 'checked' : '' }}></td>
                    <td style="text-align: center;"><input type="checkbox" name="low_slack" value="1" {{ (\App\Models\Setting::get('low_slack', '0') == '1') ? 'checked' : '' }}></td>
                </tr>
            </tbody>
        </table>

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
        form.addEventListener('change', function() { // Pour les checkboxes
            saveBtn.disabled = false;
            saveBtn.classList.add('btn-active-state');
        });
    }
</script>

@endsection