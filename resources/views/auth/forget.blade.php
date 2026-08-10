@extends('layout.authw')
@section('title', 'Vérification')
@section('content')

    {{-- 1. Le conteneur principal qui centre tout (existe dans ton CSS) --}}
    <div class="background image-left">
        
        {{-- 2. L'image de gauche (existe dans ton CSS) --}}
        <img src="{{ asset('images/forgot.png') }}" alt="" class="shap">

        {{-- 3. Le cadre blanc de droite (existe dans ton CSS) --}}
        <div class="login-card">
            
            {{-- 4. Le titre divisé en deux avec un saut de ligne <br> --}}
            <h2>Forget<br>Your Password</h2>

            <form action="{{ route('forget') }}" method="POST">
                
                @csrf {{-- Sécurité Laravel obligatoire --}}
                
                <div class="input-group">
                    <label>Email *</label>
                    <input type="email" name="email" required value="{{ old('email') }}">
                </div>

                {{-- 5. Un petit div pour forcer le centrage du bouton --}}
                <div style="text-align: center; margin-top: 30px;">
                    <button type="submit" class="login-btnn">
                        Envoyer le lien
                    </button>
                </div>

            </form>
        </div>

    </div>

@endsection
            
