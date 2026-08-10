<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="{{ asset('style/login.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>

<body>
    <div class="app-layout">
        @include('layout.sidebar')
        <div class="main-content">
            @include('layout.topbar')
            <main class="dashboard-content">
                @yield('content')
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script src="{{ asset('javas/login.js') }}"></script>
    
</body>

</html>