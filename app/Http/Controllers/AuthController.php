<?php
namespace App\Http\Controllers;
class AuthController extends Controller 

    public function getLogin(){
        $request->validate({
            'username' => 'required|email'
            'password' => 'required|string'

        });
        
    return view('layout.dashboard');
    }
?>