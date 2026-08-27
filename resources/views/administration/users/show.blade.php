@extends('layout.app')

@section('content')

<div class="page-title">
    <h1>Détails de l'utilisateur</h1>
</div>

<div class="entity-details">

    <div class="entity-details-header">
        <div>
            <h2>{{ $user->name }}</h2>
            <p>{{ $user->email }}</p>
        </div>
        <a href="{{ route('users') }}" class="btn btn-cancel">
            <i class="fa-solid fa-arrow-left"></i>
            Retour
        </a>
    </div>

    <div class="entity-details-body">
        <div class="details-grid">

            <div class="detail-item">
                <span class="detail-label">Nom</span>
                <span class="detail-value">{{ $user->name }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Email</span>
                <span class="detail-value">{{ $user->email }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Téléphone</span>
                <span class="detail-value">{{ $user->telephone ?? '—' }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Fonction</span>
                <span class="detail-value">{{ $user->role }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Département</span>
                <span class="detail-value">{{ $user->department ?? '—' }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Statut</span>
                <span class="detail-value">{{ $user->status }}</span>
            </div>

        </div>
    </div>

</div>

@endsection