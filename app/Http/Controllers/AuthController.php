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
use App\Mail\SignMail; 
use App\Models\AuditLog;

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

            // Enregistrer la dernière connexion
            $user = Auth::user();
            $user->last_login = now();
            $user->save();
            AuditLog::create([
                'user_id'      => $user->id,
                'action'       => 'auth_login_success',
                'resource_type'=> 'User',
                'resource_id'  => $user->id,
                'ip_address'   => $request->ip(),
                'is_success'   => true,
            ]);
            return redirect()->route('dashboard');
        }
        AuditLog::create([
            'user_id'      => null, // null car on ne connaît pas l'utilisateur
            'action'       => 'auth_login_failed',
            'resource_type'=> 'User',
            'ip_address'   => $request->ip(),
            'is_success'   => false,
            'details'      => 'Tentative avec email : ' . $request->email,
        ]);
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

        // utilisateur inactif 
        $user->status = 'inactif';
        // On met un mot de passe temporaire absurde (il sera écrasé quand il cliquera sur le lien)
        $user->password = Hash::make(Str::random(32));
        
        // pour afficher les erreurs 
        try {
            $user->save(); // On essaie de sauvegarder
        } catch (\Illuminate\Database\QueryException $e) {
            // Si c'est une erreur de doublon (téléphone ou email), on renvoie une erreur propre
            if ($e->getCode() == 23505) { // Code PostgreSQL pour "Unique violation"
                return back()->withErrors(['telephone' => 'Ce numéro de téléphone ou cet email est déjà utilisé.'])->withInput();
            }
            throw $e; // Si c'est une autre erreur, on la relance normalement
        }
        

        // PasswordFacade pour créer le token
        $token = PasswordFacade::createToken($user);

        // Envoi de l'email personnalisé
        Mail::to($user->email)->send(
            new SignMail($user, $token)
        );

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
            Mail::to($user->email)->send(new SignMail($user, $token));
        }

        return redirect()->route('look')
            ->with('status', 'Le lien de création de mot de passe a été renvoyé.')
            ->with('email', $request->email)
            ->with('type', 'register');
    }     

    // --- RESET PASSWORD (MARCHE POUR LES DEUX LIENS) ---

    public function showResetPassword(Request $request, string $token)
    {
        // validité du lien
        $user= User::where("email", $request->email)->first();

        if (!$user) {
            abort(403, 'User Not Found');
        }

        $is_valid = PasswordFacade::getRepository()->exists($user, $token);

        if (!$is_valid) {
            abort(403, 'Invalid Link');
        }
        return view('auth.password', [
            'token' => $token,
            'email' => $request->query('email'),
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
                    'password' => Hash::make($password), // On sauvegarde le VRAI mot de passe
                    'status' => 'actif',
                ])->setRememberToken(Str::random(60));

                
                $user->save();
            }
        );

        return $status === PasswordFacade::PASSWORD_RESET
            ? redirect()->route('login') // Mot de passe créé avec succès -> LOGIN
            : back()->withErrors(['email' => [__($status)]]);
    }
}