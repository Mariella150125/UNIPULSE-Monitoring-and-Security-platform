<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;

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

            return redirect()->route('content');
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
    

}
?>