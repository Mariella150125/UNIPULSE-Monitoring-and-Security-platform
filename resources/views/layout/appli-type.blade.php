@extends('layout.app')

@section('content')

    <a href="{{ route('settings') }}" class="btn btn-cancel" style="margin-bottom: 15px; display: inline-flex;">
        <i class="fa-solid fa-arrow-left"></i> Retour aux paramètres
    </a>
    <div class="page-title">

        <div>
            <h1>Types d'applications</h1>
            <p>
                Gérez les types d'applications disponibles dans la plateforme.
            </p>
        </div>

        <button
            type="button"
            class="usr-btn"
            data-modal-open="application-type-modal"
        >
            <i class="fa-solid fa-plus"></i>
            Ajouter un type
        </button>
        

    </div>


    {{-- Message de succès --}}
    @if(session('success'))

        <div class="alert alert-success">
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


    <div class="panel">

        <table class="server-table">

            <thead>

                <tr>
                    <th>Nom</th>
                    <th>Description</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>

            </thead>

            <tbody>

                @forelse($applicationTypes as $type)

                    <tr>

                        <td>
                            <strong>
                                {{ $type->name }}
                            </strong>
                        </td>

                        <td>
                            {{ $type->description ?? '—' }}
                        </td>

                        <td>

                            @if($type->status)

                                <span class="status-badge status-active">
                                    Actif
                                </span>

                            @else

                                <span class="status-badge status-inactive">
                                    Inactif
                                </span>

                            @endif

                        </td>

                        <td>

                            <div class="table-actions">

                                <button
                                    type="button"
                                    class="icon-action"
                                    title="Modifier"
                                >
                                    <i class="fa-solid fa-pen"></i>
                                </button>

                                @if($type->status)

                                    <form
                                        action="{{ route(
                                            'application-types.destroy',
                                            $type
                                        ) }}"
                                        method="POST"
                                    >

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="icon-action danger"
                                            title="Désactiver"
                                        >
                                            <i class="fa-solid fa-ban"></i>
                                        </button>

                                    </form>

                                @endif

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="4"
                            class="empty-state"
                        >
                            Aucun type d'application enregistré.
                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>


    {{-- ======================================================
        MODALE AJOUT TYPE
        ====================================================== --}}

    <x-modal
        id="application-type-modal"
        title="Ajouter un type d'application"
    >

        <form
            action="{{ route('application-types.store') }}"
            method="POST"
            id="application-type-form"
        >

            @csrf

            <div class="input-group">

                <label for="type-name">
                    Nom *
                </label>

                <input
                    type="text"
                    id="type-name"
                    name="name"
                    placeholder="Ex : Web, Mobile, API..."
                    required
                >

            </div>


            <div class="input-group">

                <label for="type-description">
                    Description
                </label>

                <textarea
                    id="type-description"
                    name="description"
                    rows="4"
                    placeholder="Description du type d'application..."
                ></textarea>

            </div>

        </form>


        <x-slot:footer>

            <button
                type="button"
                class="btn btn-cancel"
                data-modal-close="application-type-modal"
            >
                Annuler
            </button>

            <button
                type="submit"
                form="application-type-form"
                class="btn btn-primary"
            >
                Enregistrer
            </button>

        </x-slot:footer>

    </x-modal>

@endsection