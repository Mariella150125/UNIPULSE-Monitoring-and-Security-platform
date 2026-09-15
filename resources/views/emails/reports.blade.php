<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; color: #333;">
    <div style="background: #1d4a40; color: #fff; padding: 20px; text-align: center;">
        <h1>{{ $data['title'] }}</h1>
    </div>
    <div style="padding: 20px;">
        <p>Bonjour,</p>
        <p>Veuillez trouver ci-joint le rapport automatique généré le {{ $data['date'] }}.</p>
        <p>Cordialement,<br>L'équipe UNIPULSE</p>
    </div>
</body>
</html>