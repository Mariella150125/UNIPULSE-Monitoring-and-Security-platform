@extends('layout.app')

@section('content')

<div class="page-title">
    <h1>Supprimer le connecteur</h1>
    <p>Confirmation de suppression</p>
</div>

<div class="delete-card">

    <div class="delete-icon">
        <i class="fa-solid fa-triangle-exclamation"></i>
    </div>

    <h2>Voulez-vous supprimer ce connecteur ?</h2>

    <p>Vous êtes sur le point de supprimer :</p>

    <strong>
        @if ($connector->type === 'prometheus')
            <i class="fa-solid fa-chart-line"></i> 
        @else
            <i class="fa-solid fa-shield-halved"></i> 
        @endif
        {{ $connector->name }}
    </strong>

    <p style="color:var(--text-muted);font-size:14px;">
        {{ $connector->full_url }}
    </p>

    <p class="warning-text">
        Cette action est irréversible. L'historique des tests de connexion sera également supprimé.
    </p>

    <div class="form-actions">

        <a href="{{ route('connectors.show', $connector) }}" class="btn-cancel">
            Annuler
        </a>

        <form
            action="{{ route('connectors.destroy', $connector) }}"
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