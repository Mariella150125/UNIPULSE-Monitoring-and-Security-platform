<div class="delete-modal">

    <h2>{{ $title ?? 'Confirmer la suppression' }}</h2>

    <p>
        Voulez-vous vraiment supprimer
        <strong>{{ $name }}</strong> ?
    </p>

    <p>
        Cette action est irréversible.
    </p>

    <form action="{{ $action }}" method="POST">

        @csrf
        @method('DELETE')

        <button type="button" class="btn btn-cancel" >
            Annuler
        </button>

        <button type="submit" class="btn btn-danger">
            Supprimer
        </button>

    </form>

</div>