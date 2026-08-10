@extends('layout.auth')
@section('title','Connexion')
@section('content')
    
       
    <form method="POST" action="{{ route('login') }}" id="login-form" novalidate>
                @csrf
                <h1> SIGN IN </h1>
                <div class="input-group">
                    <input type="text"
                        name="username"
                        id="username"
                        placeholder="Username"
                        value="{{ old('username') }}"
                        autocomplete="username"
                        required>

                    @error('username')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="input-group password">
                    <input type="password"
                        name="password"
                        id="login-password"
                        placeholder="Password"
                        autocomplete="new-password"
                        required>

                    <i class="fa-solid fa-eye" id="login-toggle-password"></i>

                    @error('password')
                        <span class="field-error">{{ $message }}</span>
                    @enderror

                    {{-- Panneau de critères de mot de passe, piloté par login.js --}}
                    <ul class="password-rules" id="login-password-rules" hidden>
                        <li data-rule="length">Au moins 8 caractères</li>
                        <li data-rule="uppercase">Une lettre majuscule</li>
                        <li data-rule="lowercase">Une lettre minuscule</li>
                        <li data-rule="number">Un chiffre</li>
                        <li data-rule="special">Un caractère spécial (!@#$...)</li>
                    </ul>
                </div>

                <button type="submit" class="login-btn" id="btn-login">
                    LOGIN
                </button>

                <div class="bottom">

                    <label class="remember">
                        <input type="checkbox" name="remember" id="remember">
                        <span class="remember-dot"></span>
                        Remember
                    </label>

                    <a href="#" class="create">
                        Sign Up
                    </a>

                </div>

            </form>

            <div class="forgot">
                <a href="#">
                    Forgot password?
                </a>
            </div>

        </div>

    </form>
@endsection