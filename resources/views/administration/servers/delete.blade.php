@extends('layout.app')

@section('content')

<div class="page-title">
    <h1>Supprimer l'utilisateur</h1>
    <p>Confirmation de suppression</p>
</div>

<div class="delete-card">

    <div class="delete-icon">
        <i class="fa-solid fa-triangle-exclamation"></i>
    </div>

    <h2>Voulez-vous supprimer cet utilisateur ?</h2>

    <p>
        Vous êtes sur le point de supprimer :
    </p>

    <strong>{{ $user->name }}</strong>

    <p class="warning-text">
        Cette action est irréversible.
    </p>

    <div class="form-actions">

        <a href="{{ route('users') }}" class="btn-cancel">
            Annuler
        </a>

        <form
            action="{{ route('users.destroy', $user->id) }}"
            method="POST"
        >
            @csrf
            @method('DELETE')

            <button type="submit" class="btn-delete">
                <i class="fa-solid fa-trash"></i>
                Supprimer
            </button>
        </form>

    </div>

</div>

@endsection