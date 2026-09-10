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
                    <form action="{{ route('application-groups.destroy', $group) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="icon-btn" style="color: var(--red); border: none; background: transparent; cursor: pointer;" onclick="return confirm('Supprimer ce groupe ?')">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
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

@endsection