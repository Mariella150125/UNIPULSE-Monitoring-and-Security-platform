@extends('layout.app')

@section('content')

<div class="page-title">
    <a href="{{ route('settings') }}" class="btn btn-cancel">
        <i class="fa-solid fa-arrow-left"></i> Retour aux paramètres
    </a>
    <h1>Fenêtres de maintenance</h1>
    <p>Suspendre temporairement la génération d'alertes (MF-168).</p>
</div>

@if(session('success'))
    <div class="success-message">
        <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
    </div>
@endif

<!-- Formulaire de planification -->
<div class="panel" style="margin-bottom: 24px;">
    <div class="panel-header">
        <p>Planifier une nouvelle maintenance</p>
    </div>

    <form id="settingsForm" method="POST" action="{{ route('settings.maintenance.store') }}">
        @csrf
        @method('PUT') 
            <div class="settings-form-grid" style="margin-top: 20px;">
                <div class="form-group">
                    <label>Type de ressource</label>
                    <select name="resource_type" id="resource_type_select" class="form-control">
                        <option value="server">Serveur</option>
                        <option value="application">Application</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Ressource concernée</label>
                    <select name="resource_name" id="resource_name_select" class="form-control" required>
                        {{-- Les options seront remplies par le JavaScript --}}
                    </select>
                </div>

                <div class="form-group">
                    <label>Date et heure de début</label>
                    <input type="datetime-local" name="start_time" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Date et heure de fin</label>
                    <input type="datetime-local" name="end_time" class="form-control" required>
                </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Activer la maintenance</button>
        </div>
    </form>
</div>

<!-- Liste des maintenances -->
<div class="panel">
    <div class="panel-header">
        <p>Maintenances planifiées et passées</p>
    </div>
    
    <table class="server-table">
        <thead>
            <tr>
                <th>Ressource</th>
                <th>Type</th>
                <th>Début</th>
                <th>Fin</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($maintenances as $maintenance)
                <tr>
                    <td><strong>{{ $maintenance->resource_name }}</strong></td>
                    <td>
                        @if($maintenance->resource_type == 'server')
                            <span class="badge badge-success">Serveur</span>
                        @else
                            <span class="badge badge-major">Application</span>
                        @endif
                    </td>
                    <td>{{ \Carbon\Carbon::parse($maintenance->start_time)->format('d/m/Y H:i') }}</td>
                    <td>{{ \Carbon\Carbon::parse($maintenance->end_time)->format('d/m/Y H:i') }}</td>
                    <td>
                        @php
                            $now = now();
                            if (!$maintenance->is_active) {
                                $status = 'Annulée';
                                $class = 'badge-critical';
                            } elseif ($now->between($maintenance->start_time, $maintenance->end_time)) {
                                $status = 'En cours';
                                $class = 'badge-major';
                            } elseif ($now->lt($maintenance->start_time)) {
                                $status = 'À venir';
                                $class = 'badge-success';
                            } else {
                                $status = 'Terminée';
                                $class = '';
                            }
                        @endphp
                        <span class="badge {{ $class }}">{{ $status }}</span>
                    </td>
                    <td>
                        @if($maintenance->is_active && $now->lt($maintenance->start_time))
                            <form action="{{ route('settings.maintenance.destroy', $maintenance->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Annuler</button>
                            </form>
                        @else
                            <button class="btn btn-cancel" disabled>Supprimer</button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px; color: var(--text-muted);">
                        Aucune maintenance planifiée.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<script>
    // On récupère les listes depuis Laravel vers JavaScript
    const servers = @json($servers);
    const applications = @json($applications);

    const typeSelect = document.getElementById('resource_type_select');
    const nameSelect = document.getElementById('resource_name_select');

    function updateResourceNames() {
        // On vide la liste
        nameSelect.innerHTML = '';

        // On choisit la bonne liste selon le type
        const list = typeSelect.value === 'server' ? servers : applications;

        // On remplit la liste déroulante
        list.forEach(function(name) {
            const option = document.createElement('option');
            option.value = name;
            option.textContent = name;
            nameSelect.appendChild(option);
        });
    }

    // Quand on change le type (Serveur/Application), on met à jour la liste
    typeSelect.addEventListener('change', updateResourceNames);

    // On remplit la liste une première fois au chargement de la page
    updateResourceNames();
</script>
@endsection