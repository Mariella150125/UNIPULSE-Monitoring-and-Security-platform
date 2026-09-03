<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniPulse</title>
    <link rel="stylesheet" href="{{ asset('style/splash.css') }}">
</head>
<body>
    <main class="splash-container">
        <div class="splash-logo">
            <img src="{{ asset('images/unipulse.png') }}" alt="UniPulse">
        </div>
        <h1>UniPulse</h1>
        <p class="tagline">Monitor. Secure. Perform.</p>
        <div class="loading-section">
            <p id="loading-text">Initialisation de la plateforme...</p>
            <div class="progress-container">
                <div class="progress-bar" id="progress-bar"></div>
            </div>
            <span id="progress-value">0%</span>
        </div>
        <p class="platform-description">Application Security & Monitoring Correlation</p>
    </main>
    <script src="{{ asset('javas/splash.js') }}"></script>
</body>
</html>