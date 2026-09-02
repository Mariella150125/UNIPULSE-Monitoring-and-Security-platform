@extends('layout.app')

@section('content')

<div class="page-title">
    <h1>Supprimer l'application</h1>
    <p>Confirmation de suppression</p>
</div>

<div class="delete-card">
    <div class="delete-icon">
        <i class="fa-solid fa-triangle-exclamation"></i>
    </div>

    <h2>Voulez-vous supprimer cette application ?</h2>

    <p>Vous êtes sur le point de supprimer :</p>

    <strong>{{ $application->name }}</strong>

    <p class="warning-text">Cette action est irréversible.</p>

    <div class="delete-actions">
        <a href="{{ route('appli.index') }}" class=" btn btn-cancel">Annuler</a>

        <form action="{{ route('appli.destroy', $application->id) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-delete">
                <i class="fa-solid fa-trash"></i> Supprimer
            </button>
        </form>
    </div>
</div>

@endsection