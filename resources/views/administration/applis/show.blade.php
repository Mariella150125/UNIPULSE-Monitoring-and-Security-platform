@extends('layout.app')

@section('content')

<div class="page-title">
    <h1>Détails de l'utilisateur</h1>
</div>

<div class="details-card">

    <div>
        <strong>Nom</strong>
        <p>{{ $user->name }}</p>
    </div>

    <div>
        <strong>Email</strong>
        <p>{{ $user->email }}</p>
    </div>

    <div>
        <strong>Téléphone</strong>
        <p>{{ $user->telephone }}</p>
    </div>

    <div>
        <strong>Fonction</strong>
        <p>{{ $user->role }}</p>
    </div>

    <div>
        <strong>Département</strong>
        <p>{{ $user->department }}</p>
    </div>

    <div>
        <strong>Statut</strong>
        <p>{{ $user->status }}</p>
    </div>

</div>

@endsection