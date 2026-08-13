<!DOCTYPE html>
<html>
<body>
    <h1>Bienvenue, {{ $user->name }} !</h1>
    <p>Votre compte a été créé avec succès pour le département {{ $user->department }}.</p>
    
    <p>Pour finaliser votre inscription et définir votre mot de passe, cliquez sur le bouton ci-dessous :</p>
    
    <p style="text-align: center; margin: 30px 0;">
        <a href="{{ route('password.reset', ['token' => $token, 'email' => $user->email]) }}" 
           style="background-color: #4CAF50; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;">
            Définir mon mot de passe
        </a>
    </p>
    
    <p>Si le bouton ne fonctionne pas, copiez-collez ce lien dans votre navigateur :</p>
    <p>{{ route('password', ['token' => $token, 'email' => $user->email]) }}</p>
    
    <em>Ce lien expire dans 5 minutes</em>
</body>
</html>