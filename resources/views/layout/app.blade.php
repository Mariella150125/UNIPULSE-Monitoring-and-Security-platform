<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="{{ asset('style/login.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- LES LIBRAIRIES SONT BIEN ICI, DANS LE HEAD -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>

<!-- POP-UP D'ALERTE CRITIQUE -->
<div id="critical-popup" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; border: 2px solid var(--red); border-radius: 12px; padding: 30px; z-index: 10000; box-shadow: 0 0 50px rgba(192, 57, 43, 0.5); text-align: center; width: 400px;">
    <i class="fa-solid fa-triangle-exclamation" style="font-size: 50px; color: var(--red); margin-bottom: 20px; animation: shake 0.5s infinite;"></i>
    <h2 style="color: var(--red); margin: 0 0 10px;">ALERTE CRITIQUE</h2>
    <h3 id="popup-title" style="margin: 0 0 15px; color: var(--text-dark);">Transaction bloquée</h3>
    <p id="popup-description" style="color: var(--text-muted); margin-bottom: 25px;">Intervention requise immédiatement !</p>
    <button onclick="stopUrgentAlert()" class="btn btn-danger" style="width: 100%; padding: 12px; font-size: 16px;">
        <i class="fa-solid fa-check"></i> Acquitter l'alerte
    </button>
</div>

<!-- LE BOUTON DE DÉBLOCAGE DU SON -->
<button id="unlock-sound-btn" style="display:none; position:fixed; bottom:20px; right:20px; background:var(--red); color:white; border:none; padding:15px; border-radius:50px; z-index:9999; box-shadow:0 4px 15px rgba(0,0,0,0.3); cursor:pointer; font-weight:bold;">
    <i class="fa-solid fa-bell"></i> Activer l'alerte sonore
</button>

<style>
    @keyframes shake {
        0% { transform: translate(-50%, -50%) rotate(0deg); }
        25% { transform: translate(-50%, -50%) rotate(-5deg); }
        75% { transform: translate(-50%, -50%) rotate(5deg); }
        100% { transform: translate(-50%, -50%) rotate(0deg); }
    }
</style>

<body>
    <div class="app-layout">
        @include('layout.sidebar')
        <div class="sidebar-overlay" id="sidebar-overlay"></div>
        <div class="main-content">
            @include('layout.topbar')
             
            <main class="dashboard-content">
               @yield('content')
            </main>
        </div>
    </div>
    
    <!-- TON FICHIER JS EXTERNE -->
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

            // SÉCURISATION DU BOUTON DE DÉBLOCAGE DU SON
            const unlockBtn = document.getElementById('unlock-sound-btn');
            if (unlockBtn) {
                unlockBtn.addEventListener('click', function() {
                    const sound = document.getElementById('alert-sound');
                    if (sound) {
                        sound.play().then(() => {
                            this.style.display = 'none';
                        });
                    }
                });
            }
            
        });
    </script>

    <!-- Le fichier son -->
    <audio id="alert-sound" preload="auto">
        <source src="{{ asset('sounds/alert.mp3') }}" type="audio/mpeg">
    </audio>

    <!-- Le style pour le clignotement de l'écran -->
    <style>
        @keyframes screen-flash {
            0% { box-shadow: inset 0 0 0 0px rgba(192, 57, 43, 0); }
            50% { box-shadow: inset 0 0 100px 10px rgba(192, 57, 43, 0.3); }
            100% { box-shadow: inset 0 0 0 0px rgba(192, 57, 43, 0); }
        }
        body.critical-alert-flash {
            animation: screen-flash 1.5s infinite;
        }
    </style>

</body>

</html>