<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Password as PasswordFacade; // <--- LE SURNOM CORRIGE TOUT
use App\Models\User;
use App\Mail\WelcomeMail; // <--- AJOUT MANQUANT

class AuthController extends Controller 
{
    public function showLoginForm(Request $request){
        return view('auth.login');
    }

    public function signup(Request $request){
        return view('auth.sign');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
        
        if (Auth::attempt($credentials, $request->boolean('remember'))){
            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }
        
        throw ValidationException::withMessages([
            'email' => 'Identifiants incorrects.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    // --- FORGET PASSWORD ---

    public function showForgetPassword()
    {
        return view('auth.forget');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        // On utilise PasswordFacade (le système d'email de Laravel)
        $status = PasswordFacade::sendResetLink($request->only('email'));

        if ($status === PasswordFacade::RESET_LINK_SENT) {
            return redirect()->route('look')
                ->with('status', 'Si un compte existe avec cet email, un lien a été envoyé.')
                ->with('email', $request->email)
                ->with('type', 'forgot'); 
        }

        return back()->withErrors(['email' => __($status)])->withInput();
    }

    // --- SIGN UP (INSCRIPTION) ---

    public function register()
    {
        return view('auth.sign');
    }

    public function store(Request $request)
    {
        // Plus de 'password' dans la validation
        $request->validate([
            'name'      => 'required|string',
            'telephone' => 'required|string|max:9',
            'email'     => 'required|email|unique:users,email',
            'role'      => 'required',
            'department'=> 'required',
        ]);
        
        $user = new User();
        $user->name = $request->name;
        $user->telephone = $request->telephone;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->department = $request->department;
        
        // On met un mot de passe temporaire absurde (il sera écrasé quand il cliquera sur le lien)
        $user->password = Hash::make(Str::random(32)); 
        $user->save();

        // On utilise PasswordFacade pour créer le token
        $token = PasswordFacade::createToken($user);

        // Envoi de l'email personnalisé
        Mail::to($user->email)->send(new WelcomeMail($user, $token));

        // ON NE CONNECTE PAS L'UTILISATEUR ICI ! On l'envoie sur la page 'look'
        return redirect()->route('look')
            ->with('status', 'Votre compte a été créé !')
            ->with('email', $user->email)
            ->with('type', 'register');
    }

    public function resendWelcomeLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        
        $user = User::where('email', $request->email)->first();
        
        if ($user) {
            $token = PasswordFacade::createToken($user);
            Mail::to($user->email)->send(new WelcomeMail($user, $token));
        }

        return redirect()->route('look')
            ->with('status', 'Le lien de création de mot de passe a été renvoyé.')
            ->with('email', $request->email)
            ->with('type', 'register');
    }     

    // --- RESET PASSWORD (MARCHE POUR LES DEUX LIENS) ---

    public function showResetPassword(Request $request, string $token)
    {
        return view('auth.password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    // LA MÉTHODE QUI MANQUAIT ET QUI ENREGISTRE LE VRAI MOT DE PASSE
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Password::min(8)], // Ici on utilise la classe Password de validation
        ]);

        // On utilise PasswordFacade pour vérifier le token et réinitialiser
        $status = PasswordFacade::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password) // On sauvegarde le VRAI mot de passe
                ])->setRememberToken(Str::random(60));
                
                $user->save();

                // C'EST ICI QU'ON LE CONNECTE POUR LA PREMIÈRE FOIS !
                Auth::login($user); 
            }
        );

        return $status === PasswordFacade::PASSWORD_RESET
            ? redirect()->route('dashboard') // Mot de passe créé avec succès -> Dashboard
            : back()->withErrors(['email' => [__($status)]]);
    }
}