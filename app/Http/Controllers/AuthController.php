<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Models\User;




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
            'password' => [
                'required',
                'string',
            ],

        ]);
        if (Auth::attempt($credentials, $request->boolean('remember'))){
            $request->session()->regenerate();

            return redirect()->route('dashboard');
        }
        throw ValidationException::withMessages([
            'email' => 'Identifiants incorrects.',
        ]);
        
    }


    //Déconnexion
    public function logout(Request $request)
    {
        Auth::logout();
        return redirect()->route('login');
    }

    //forget password
   

    // ---------------------------------------------------------
    // 1. Afficher la page où l'on entre l'email (forget.blade.php)
    // ---------------------------------------------------------
    public function showForgetPassword()
    {
        return view('auth.forget');
    }

    // ---------------------------------------------------------
    // 2. Traiter l'envoi de l'email avec le lien sécurisé
    // ---------------------------------------------------------
    public function sendResetLink(Request $request)
    {
        // On vérifie que l'email est valide
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // LA MAGIE DE LARAVEL : Cette fonction vérifie si l'email existe en base de données.
        // Si oui, elle génère un token, le sauvegarde, et ENVOIE UN EMAIL TOUT SEULE.
        // (Elle utilise l'email par défaut de Laravel, pas besoin de créer de Mailable ici !)
        $status = Password::sendResetLink(
            $request->only('email')
        );

        // Si l'email a bien été envoyé (ou si l'email n'existe pas, Laravel ment pour des raisons de sécurité)
        if ($status === Password::RESET_LINK_SENT) {
            return redirect()->route('look')
                ->with('status', 'Si un compte existe avec cet email, un lien de réinitialisation a été envoyé.')
                ->with('email', $request->email)
                ->with('type', 'forgot'); 
        }

        // S'il y a une autre erreur (ex: trop de demandes)
        return back()
            ->withErrors(['email' => __($status)])
            ->withInput();
    }
    public function resendWelcomeLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        
        $user = User::where('email', $request->email)->first();
        
        if ($user) {
            // On recrée un token et on renvoie le mail personnalisé
            $token = Password::createToken($user);
            Mail::to($user->email)->send(new WelcomeMail($user, $token));
        }

        return redirect()->route('look')
            ->with('status', 'Créer votre mot de passe')
            ->with('email', $request->email)
            ->with('type', 'register');
    }     

    // ---------------------------------------------------------
    // 3. Afficher la page pour taper le nouveau mot de passe
    // ---------------------------------------------------------
    public function showResetPassword(Request $request, string $token)
    {
        return view('auth.password', [
            'token' => $token,       // On passe le token à la vue pour le mettre dans un champ caché (hidden)
            'email' => $request->email, // On passe l'email pour le pré-remplir
        ]);
    }
    // SIGN UP
    public function register()
    {
        return view('auth.sign');
    }
    public function store(Request $request)
    {
        $request->validate([
            'name'      =>  'required | string',
            'telephone' =>  'required | string | max:9',
            'email'     =>  'required | email | unique:users,email',
            'role'      =>  'required',
            'department'=>  'required',
        ]);
        $user=new User();
        $user->name=$request->name;
        $user->telephone=$request->telephone;
        $user->email=$request->email;
        $user->role=$request->role;
        $user->department=$request->department;
        $user->password=Hash::make($request->password);
        $user->save();

        Auth::login($user);
        // 3. Générer un token sécurisé (Laravel le stocke dans la table password_reset_tokens)
        $token = Password::createToken($user);

        // 4. Envoyer l'e-mail avec le token
        Mail::to($user->email)->send(new WelcomeMail($user, $token));

        // 5. Rediriger vers la page de connexion avec un message de succès
        return redirect()->route('look')
            ->with('status', 'Votre compte a été créé!')
            ->with('email', $user->email)
            ->with('type', 'register') ;
    }

}
?>