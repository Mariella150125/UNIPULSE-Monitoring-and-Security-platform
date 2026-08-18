@extends('layout.auth')
@section('title', 'Activation')
@section('content')
    <h4>Créer mon mot de passe</h4>
   <form action="{{ route('password.update') }}" method="POST" id="activation-form">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">

        <div class="input-group password">
            <label>Nouveau mot de passe</label>
            <input type="password"
                name="password"
                id="activation-password"
                placeholder="Password"
                autocomplete="new-password"
                required>

            <i class="fa-solid fa-eye" id="activation-toggle-password"></i>

            @error('password')
                <span class="field-error">{{ $message }}</span>
            @enderror

            <ul class="password-rules" id="activation-password-rules" hidden>
                <li data-rule="length">Au moins 8 caractères</li>
                <li data-rule="uppercase">Une lettre majuscule</li>
                <li data-rule="lowercase">Une lettre minuscule</li>
                <li data-rule="number">Un chiffre</li>
                <li data-rule="special">Un caractère spécial (!@#$...)</li>
            </ul>
        </div>

        <div class="input-group password">
            <label>Confirmer le mot de passe</label>
            <input type="password"
                name="password_confirmation"
                id="password-confirmation"
                placeholder="Password"
                autocomplete="new-password"
                required>

            <i class="fa-solid fa-eye" id="confirmation-toggle-password"></i>

            @error('password_confirmation')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="login-btnn">
            Activer mon compte
        </button>
    </form> 
@endsection        