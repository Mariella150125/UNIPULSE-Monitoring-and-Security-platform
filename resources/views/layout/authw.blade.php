<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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