@extends('layout.auth')
@section('title', 'Inscription')
@section('content')

    <h1>Créer un compte</h1>

    <form action="{{ route('sign') }}" method="POST" id="signup-form">
        @csrf
        
        {{-- ==================== ÉTAPE 1 ==================== --}}
        <div class="form-step" data-step="1">
            <div class="card">
                <h3>Informations personnelles</h3>

                <div class="input-group">
                    <label>Nom *</label>
                    <input type="text" name="last_name" placeholder="Full Name" required>
                </div>

                <div class="input-group">
                    <label>Email *</label>
                    <input type="email" name="email" required>
                </div>

                <div class="input-group">
                    <label>Téléphone</label>
                    <input type="text" name="phone">
                </div>
            </div>
            <div class="form-navigation">
                <button type="button" class="login-btn next-btn">
                    Suivant
                </button>
            </div>
        </div>

        {{-- ==================== ÉTAPE 2 ==================== --}}
        <div class="form-step" data-step="2" hidden>
            <div class="card">
                <h3>Affectation</h3>

                <div class="input-group">
                    <label>Fonction</label>
                    <input type="text" name="Fonction">
                </div>

                <div class="input-group">
                    <label>Département</label>
                    <select name="département">
                        <option>Application Support</option>
                        <option>Software Development</option>
                        <option>DevOps</option>
                        <option>Sécurité de l'information</option>
                    </select>
                </div>

                <label class="checkbox">
                    <input type="checkbox" checked name="send_link">
                    Envoyer un lien d'activation
                </label>
                
            </div>
            <div class="form-navigation">
                <button type="button" class="buttons prev-btn">
                    Retour
                </button>
                <button type="submit" class="login-btn" id="btn-login">
                    Créer
                </button>    
            </div>
        </div>                 
    </form>

@endsection