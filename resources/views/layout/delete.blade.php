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

        <button type="button" class="login-btn" >
            Annuler
        </button>

        <button type="submit" class="login-btn">
            Supprimer
        </button>

    </form>

</div>