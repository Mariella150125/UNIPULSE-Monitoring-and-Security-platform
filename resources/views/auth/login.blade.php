@extends('layout.auth')

@section('title', 'Connexion')

@section('content')

@if(session('status'))
    <div class="status-color">
         {{ session('status') }}
    </div>   
@endif
<form id="login-form" method="POST" action="{{ route('login.submit') }}">

    @csrf

    <h1>SIGN IN</h1>

    <div class="input-group">

        <input
            type="email"
            name="email"
            id="username"
            placeholder="email"
            value="{{ old('email') }}"
            autocomplete="username"
            required
        >

        @error('email')
            <span class="field-error">
                {{ $message }}
            </span>
        @enderror

    </div>


    <div class="input-group password">

        <input
            type="password"
            name="password"
            id="login-password"
            placeholder="Password"
            autocomplete="current-password"
            required
        >

        <i
            class="fa-solid fa-eye"
            id="login-toggle-password">
        </i>

        @error('password')
            <span class="field-error">
                {{ $message }}
            </span>
        @enderror

    </div>


    <button
        type="submit"
        class="login-btn"
        id="btn-login"
    >
        LOGIN
    </button>


    <div class="bottom">

        <label class="remember">

            <input
                type="checkbox"
                name="remember"
                id="remember"
            >

            <span class="remember-dot"></span>

            Remember

        </label>

    </div>

    <div class="forgot">
        <a href="{{ route('password.request') }}">
            Forgot password?
        </a>
    </div>

</form>

@endsection