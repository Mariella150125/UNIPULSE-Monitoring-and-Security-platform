@extends('layout.app')

@section('content')

<div class="page-title">
    <h1>Supprimer le serveur</h1>
    <p>Confirmation de suppression</p>
</div>

<div class="delete-card">

    <div class="delete-icon">
        <i class="fa-solid fa-triangle-exclamation"></i>
    </div>

    <h2>Voulez-vous supprimer ce serveur ?</h2>

    <p>Vous êtes sur le point de supprimer :</p>

    <strong>{{ $server->name }} ({{ $server->hostname }})</strong>

    <p style="color:var(--text-muted);font-size:14px;">
        {{ $server->ip_address }}{{ $server->port ? ':' . $server->port : '' }}
    </p>

    @if ($server->applications->isNotEmpty())
        <p class="warning-text">
            Attention : {{ $server->applications->count() }} application(s) sont hébergée(s) sur ce serveur et seront dissociée(s).
        </p>
    @else
        <p class="warning-text">
            Cette action est irréversible.
        </p>
    @endif

    <div class="delete-actions">
        
        {{-- CORRECTION 1 : Retirer le $server de la route server.index --}}
        <a href="{{ route('server.index') }}" class="btn btn-cancel">
            Annuler
        </a>

        <form action="{{ route('server.destroy', $server->id) }}" method="POST">
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