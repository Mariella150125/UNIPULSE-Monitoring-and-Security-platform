<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller 

    public function login(Request $request){
        $request->validate({
            'username' => 'required|email'
            'password' => 'required|string'

        });
        if (Auth::attempt($request->only('email', 'password'))){
            $request->session()->regenerate();

            return view('layout.dashboard');
        }
        throw ValidationException::withMessages([
            'email' => 'Identifiants incorrects.',
        ]);

    }
?>