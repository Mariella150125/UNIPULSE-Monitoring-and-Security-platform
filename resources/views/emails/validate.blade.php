<!DOCTYPE html>
<html>
<body>
    <h1>Bienvenue, {{ $user->name }} 🎉 !</h1>
    <p>Votre compte a été créé avec succès pour le département {{ $user->department }}.</p>
    
    <p>Pour finaliser votre inscription et définir votre mot de passe, cliquez sur le bouton ci-dessous :</p>
    
    <p style="text-align: center; margin: 30px 0;">
        <a href="{{ url('/password/' . $token . '?email=' . urlencode($user->email)) }}" class="button">
            Définir mon mot de passe
        </a>
    </p>
    
    <p>Si le bouton ne fonctionne pas, copiez-collez ce lien dans votre navigateur :</p>
    <p style="word-break: break-all; font-size: 12px; color: #56825E;">
        {{ url('/password/' . $token . '?email=' . urlencode($user->email)) }}
    </p>
    <em>Ce lien expire dans 5 minutes</em>
</body>
</html>