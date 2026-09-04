
@extends('layout.app')
@section('content')

    <div class="coming-soon-container">


    <div class="coming-soon-card">

        <div class="coming-soon-icon">
            <i class="fa-solid fa-screwdriver-wrench"></i>
        </div>

        <h1>Fonctionnalité en cours de développement</h1>

        <p>
            Cette fonctionnalité n'est pas encore disponible.
            Elle est actuellement en cours d'intégration à la plateforme.
        </p>

        <a href="{{ url('/content') }}" class="coming-soon-btn">
            <i class="fa-solid fa-arrow-left"></i>
            Retour au tableau de bord
        </a>

    </div>

    </div>
@endsection