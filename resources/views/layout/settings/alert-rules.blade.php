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



<form id="settingsForm" method="POST" action="{{ route('settings.update') }}">
    @csrf
    @method('PUT')
    
    <h2>Seuils d'alerte Serveurs</h2>
    <table class="server-table">
        <thead>
            <tr>
                <th>Métrique</th>
                <th>Niveau Avertissement (%)</th>
                <th>Niveau Critique (%)</th>
            </tr>
        </thead>
        <tbody>
            <!-- CPU -->
            <tr>
                <td><strong>CPU</strong></td>
                <td><input type="number" name="cpu_warning" value="{{ old('cpu_warning', \App\Models\Setting::get('cpu_warning', 75)) }}" class="form-control"></td>
                <td><input type="number" name="cpu_critical" value="{{ old('cpu_critical', \App\Models\Setting::get('cpu_critical', 90)) }}" class="form-control"></td>
            </tr>
            <!-- RAM -->
            <tr>
                <td><strong>RAM</strong></td>
                <td><input type="number" name="ram_warning" value="{{ old('ram_warning', \App\Models\Setting::get('ram_warning', 80)) }}" class="form-control"></td>
                <td><input type="number" name="ram_critical" value="{{ old('ram_critical', \App\Models\Setting::get('ram_critical', 90)) }}" class="form-control"></td>
            </tr>
            <!-- DISK -->
            <tr>
                <td><strong>Disque (Disk)</strong></td>
                <td><input type="number" name="disk_warning" value="{{ old('disk_warning', \App\Models\Setting::get('disk_warning', 80)) }}" class="form-control"></td>
                <td><input type="number" name="disk_critical" value="{{ old('disk_critical', \App\Models\Setting::get('disk_critical', 90)) }}" class="form-control"></td>
            </tr>
        </tbody>
    </table>

    <div class="form-actions">
        <button type="submit" class="usr-btn">Enregistrer les seuils</button>
    </div>
</form>

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