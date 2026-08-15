@extends('layout.authw')
@section('title', 'Vérification')
@section('background-class', 'image-left')
@section('content')

<div class="forgot-container">
    
    <img src="{{ asset('images/forgot.png') }}" alt="" class="shap">

    <div class="form-section">
        
        <h2>Mot de passe oublié?</h2>

        @if(session('status'))
            <div class="status-color">
                {{ session('status') }}
            </div>
        @endif

        @error('email')
            <div class="status-color">
                {{ $message }}
            </div>
        @enderror

        <form action="{{ route('password.email') }}" method="POST">
            
            @csrf
            
            <div class="input-group">
                <label>Email *</label>
                <input type="email" name="email" required value="{{ old('email') }}">
            </div>

            @if(session('status'))
                <button type="submit" class="link-btn">
                     Me renvoyer le lien
                </button>
            @else
                <button type="submit" class="link-btn">
                    M'envoyer le lien
                </button>
            @endif
                
        </form>

    </div>

</div>

@endsection