@extends('layout.app')

@section('content')

<div class="page-title">
    <a href="{{ route('settings') }}" class="btn btn-cancel" style="margin-bottom: 15px;">
        <i class="fa-solid fa-arrow-left"></i> Retour aux paramètres
    </a>
    <h1>Groupes d'Applications</h1>
</div>

@if(session('success'))
    <div class="success-message"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
@endif

<div class="panel">
    <form action="{{ route('application-groups.store') }}" method="POST" style="display: flex; gap: 10px; margin-bottom: 20px;">
        @csrf
        <input type="text" name="name" placeholder="Nom du groupe (ex: Frontend, API)" required class="form-control">
        <input type="text" name="description" placeholder="Description (optionnel)" class="form-control">
        <button type="submit" class="usr-btn"><i class="fa-solid fa-plus"></i> Ajouter</button>
    </form>

    <table class="server-table">
        <thead>
            <tr>
                <th>Nom du groupe</th>
                <th>Description</th>
                <th>Nombre d'applications</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($groups as $group)
            <tr>
                <td><strong>{{ $group->name }}</strong></td>
                <td>{{ $group->description ?? '—' }}</td>
                <td>{{ $group->applications_count }}</td>
                <td>
                    <div class="action-dropdown">
                        <button class="icon-btn action-dropdown-toggle" title="Actions">
                            <i class="fa-solid fa-ellipsis-vertical"></i>
                        </button>
                        <div class="action-dropdown-menu">
                            <a href="#" class="dropdown-item">
                                <i class="fa-solid fa-pen"></i> Modifier
                            </a>
                            <div class="dropdown-divider"></div>
                            <form action="{{ route('application-groups.destroy', $group) }}" method="POST" onsubmit="return confirm('Supprimer ce groupe ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="dropdown-item text-red">
                                    <i class="fa-solid fa-trash"></i> Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align: center; padding: 20px; color: var(--text-muted);">Aucun groupe créé.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<script>
    function editAppGroup(id, name, description) {
        // 1. On trouve le formulaire d'ajout en haut de la page
        const form = document.querySelector('form[action="{{ route('application-groups.store') }}"]');
        if (!form) return;

        // 2. On change l'URL du formulaire pour pointer vers la route de modification (UPDATE)
        form.action = '{{ route("application-groups.update", ":id") }}'.replace(':id', id);

        // 3. On ajoute la méthode PUT (nécessaire pour Laravel)
        let methodInput = form.querySelector('input[name="_method"]');
        if (!methodInput) {
            methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            form.appendChild(methodInput);
        }
        methodInput.value = 'PUT';

        // 4. On remplit les champs avec les valeurs actuelles
        form.querySelector('input[name="name"]').value = name;
        form.querySelector('input[name="description"]').value = description;

        // 5. On fait défiler la page vers le haut pour que l'utilisateur voie le formulaire
        form.scrollIntoView({ behavior: 'smooth', block: 'center' });

        // 6. On change le texte du bouton pour indiquer qu'on modifie
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="fa-solid fa-check"></i> Mettre à jour';
            submitBtn.style.backgroundColor = 'var(--orange)'; // On le met en orange pour différencier
        }

        // On met le focus sur le champ de nom
        form.querySelector('input[name="name"]').focus();
    }
</script>
@endsection