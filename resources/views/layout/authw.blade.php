<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>

    <link rel="stylesheet" href="{{ asset('style/login.css') }}">
</head>

<body class="auth-body">   
    <div class="background @yield('background-class')">
        <div class="login-card">
            @yield('content')
        </div>
    </div>
    <script src="{{ asset('javas/login.js') }}"></script>
    <script>
        
const routeToNext = (url) =>{
    window.location.href = url;
}
    </script>
</body>

</html>