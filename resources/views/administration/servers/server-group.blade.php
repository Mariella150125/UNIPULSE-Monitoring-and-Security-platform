@extends('layout.app')

@section('content')

   
    <a href="{{ route('settings') }}" class="btn btn-cancel" style="margin-bottom: 15px; display: inline-flex;">
        <i class="fa-solid fa-arrow-left"></i> Retour aux paramètres
    </a>

    <div class="page-title">
        <div>
            <h1>Groupes de serveurs</h1>
            <p>Organisez vos serveurs par groupes.</p>
        </div>

        <button type="button" class="usr-btn" data-modal-open="server-group-modal">
            <i class="fa-solid fa-plus"></i> Ajouter un groupe
        </button>
    </div>

    {{-- Message de succès --}}
    @if(session('success'))
        <div class="success-message">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- Erreurs --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ======================================================
        TABLEAU
        ====================================================== --}}
    <div class="panel">
        <div class="table-container">
            <table class="server-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Nombre de serveurs</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($serverGroups as $group)
                        <tr>
                            <td>
                                <strong>{{ $group->name }}</strong>
                            </td>
                            <td>
                                {{ $group->description ?? '—' }}
                            </td>
                            <td>
                                {{ $group->servers->count() }}
                            </td>
                            <td>
                                <div class="table-actions">
                                    <button type="button" class="icon-action" title="Modifier">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <button type="button" class="icon-action" title="Supprimer">
                                        <i class="fa-solid fa-trash" style="color: var(--red);"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty-state" style="text-align: center; padding: 30px;">
                                Aucun groupe de serveurs enregistré.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ======================================================
        MODALE AJOUT GROUPE
        ====================================================== --}}
    <x-modal id="server-group-modal" title="Ajouter un groupe de serveurs">
        <form action="{{ route('server-groups.store') }}" method="POST" id="server-group-form">
            @csrf

            <div class="input-group">
                <label for="server-group-name">Nom du groupe *</label>
                <input type="text" id="server-group-name" name="name" placeholder="Ex : Production" required>
            </div>

            <div class="input-group">
                <label for="server-group-description">Description</label>
                <textarea id="server-group-description" name="description" rows="4" placeholder="Description du groupe..."></textarea>
            </div>
        </form>

        <x-slot:footer>
            <button type="button" class="btn btn-cancel" data-modal-close="server-group-modal">
                Annuler
            </button>
            <button type="submit" form="server-group-form" class="btn btn-primary">
                Enregistrer
            </button>
        </x-slot:footer>
    </x-modal>

@endsection