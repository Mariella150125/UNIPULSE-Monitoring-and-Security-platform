@extends('layout.authw')
@section('title', 'Vérification')
@section('content')


    <h2 class="look">Vérifier votre boite mail</h2>
    <form action="{{ route('look') }}" method="POST" id="signup-form">
        @csrf
        <p> Un lien a été envoyé au </p>
        <a href="login" class="create-look">
            <button type="submit" class="login-btnn" id="btn-login">
                    Me renvoyer le lien
            </button>
    </form>
            
@endsection
 