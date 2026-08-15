@extends('layout.authw')
@section('title', 'Vérification')
@section('content')

    <h2 class="look">Vérifier votre boite mail</h2>
    @php
        $fullEmail = session('email'); 
        $type = session('type', 'forgot');
        $maskedEmail = '***@***.***'; 

        if ($fullEmail) {
            $parts = explode('@', $fullEmail);
            $name = $parts[0];
            $domainParts = explode('.', $parts[1]);
            $domain = $domainParts[0];
            $tld = isset($domainParts[1]) ? '.' . $domainParts[1] : '';
            
            $maskedEmail = substr($name, 0, 1) . str_repeat('*', strlen($name) - 1) 
                        . '@' . 
                        substr($domain, 0, 1) . str_repeat('*', strlen($domain) - 1) 
                        . $tld;
        }
    @endphp
    
    <p>Un lien a été envoyé au <strong>{{ $maskedEmail }}</strong></p>
    
    @if(session('status'))
        <div class="status-color">
            {{ session('status') }}
        </div>
    @endif

    @if($type === 'register')
        <form action="{{ route('resend.welcome') }}" method="POST">
            @csrf
            <input type="hidden" name="email" value="{{ $fullEmail }}">
            <button type="submit" class="link-btnn" id="btn-login">
                Me renvoyer le lien d'inscription
            </button>
        </form>
    @else
        <form action="{{ route('password.email') }}" method="POST">
            @csrf
            <input type="hidden" name="email" value="{{ $fullEmail }}">
            <button type="submit" class="link-btnn" id="btn-login">
                Me renvoyer le lien
            </button>
        </form>
    @endif  

@endsection