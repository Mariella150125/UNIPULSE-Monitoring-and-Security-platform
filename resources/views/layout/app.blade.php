<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="{{ asset('style/login.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <script>
        // On attend que la page finit de charger
        document.addEventListener("DOMContentLoaded", function() {
            
            // 1. Le bouton "Add Server"
            var btnOpen = document.querySelector('[data-modal-open="server-modal"]');
            if (btnOpen) {
                btnOpen.addEventListener("click", function() {
                    document.getElementById('server-modal').classList.add('open');
                    document.body.classList.add('modal-open');
                });
            }

            // 2. La croix et le bouton Annuler
            var btnsClose = document.querySelectorAll('[data-modal-close="server-modal"]');
            for (var i = 0; i < btnsClose.length; i++) {
                btnsClose[i].addEventListener("click", function() {
                    document.getElementById('server-modal').classList.remove('open');
                    document.body.classList.remove('modal-open');
                });
            }
            
        });
    </script>
</body>

</html>