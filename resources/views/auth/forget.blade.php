@extends('layout.authw')
@section('title', 'Vérification')
@section('content')
@section('background-class', 'image-left')

    <h2> <span> Forget </span> Your Password</h2>

    <form action="{{ route('forget') }}" method="POST" id="signup-form">
        <img src="{{ asset('images/forgot.png') }}" alt="" class="shap">

        <div class="card">
             <div class="input-group">
                    <label>Email *</label>
                    <input type="email" name="email" required>
             </div>
             <a href="login" class="create-look">
            <button type="submit" class="login-btnn">
                Envoyer le lien
            </button>
        </div>
    </form>
            
@endsection