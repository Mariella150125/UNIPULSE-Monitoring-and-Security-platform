@extends('layout.app')

@section('content')

<div class="page-title">
    <h1>Mon profil</h1>
    <p>Gérer les informations de votre compte</p>
</div>

<!-- Messages de succès ou d'erreur -->
@if(session('success'))
    <div class="success-message">
        <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid-2">
    
    <!-- ==============================================
         COLONNE 1 : INFOS PERSONNELLES
         ============================================== -->
    <div class="panel">
        <div class="panel-header">
            <p>Informations personnelles</p>
        </div>

        <form action="{{ route('profile.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="input-group">
                <label for="name">Nom complet *</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', auth()->user()->name) }}" required>
            </div>

            <div class="input-group">
                <label for="email">Adresse email *</label>
                <input type="email" id="email" name="email" class="form-control" value="{{ old('email', auth()->user()->email) }}" required>
            </div>

            <div class="input-group">
                <label for="telephone">Téléphone</label>
                <input type="text" id="telephone" name="telephone" class="form-control" value="{{ old('telephone', auth()->user()->telephone ?? '') }}">
            </div>

            <div class="input-group">
                <label for="department">Département</label>
                <input type="text" id="department" name="department" class="form-control" value="{{ old('department', auth()->user()->department ?? '') }}">
            </div>

            <div class="form-actions" style="border-top: none; padding-top: 0;">
                <button type="submit" class="usr-btn">
                    <i class="fa-solid fa-floppy-disk"></i> Mettre à jour
                </button>
            </div>
        </form>
    </div>

    <!-- ==============================================
         COLONNE 2 : SÉCURITÉ (Mot de passe)
         ============================================== -->
    <div class="panel">
        <div class="panel-header">
            <p>Sécurité du compte</p>
        </div>

        <form action="{{ route('profile.password.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="input-group">
                <label for="current_password">Mot de passe actuel *</label>
                <input type="password" id="current_password" name="current_password" class="form-control" required>
            </div>

            <div class="input-group">
                <label for="password">Nouveau mot de passe *</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>

            <div class="input-group">
                <label for="password_confirmation">Confirmer le mot de passe *</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
            </div>

            <div class="form-actions" style="border-top: none; padding-top: 0;">
                <button type="submit" class="usr-btn" style="background: var(--sage-green);">
                    <i class="fa-solid fa-key"></i> Changer le mot de passe
                </button>
            </div>
        </form>
    </div>
    <a href="{{ route('settings') }}" class="btn btn-cancel">
        <i class="fa-solid fa-arrow-left"></i> Retour aux paramètres
    </a>
</div>

@endsection