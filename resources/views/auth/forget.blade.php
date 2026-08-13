@extends('layout.authw')
@section('title', 'Vérification')
@section('background-class', 'image-left')
@section('content')

        
        {{-- 2. L'image de gauche (existe dans ton CSS) --}}
        <img src="{{ asset('images/forgot.png') }}" alt="" class="shap">

        
            
            {{-- 4. Le titre divisé en deux avec un saut de ligne <br> --}}
            <h2>
                Mot de passe oublié?
            </h2>
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
                
                @csrf {{-- Sécurité Laravel obligatoire --}}
                
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


@endsection
            
