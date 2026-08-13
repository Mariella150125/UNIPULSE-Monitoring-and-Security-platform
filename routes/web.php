
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// 1. Afficher la page (GET)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');

// 2. Traiter le bouton Se connecter (POST)
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

// 3. Déconnexion
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// 4. Le dashboard (protégé)
Route::get('/content', function () {
    return view('layout/dashboard');
})->middleware('auth')->name('dashboard');

Route::get('/forget', [AuthController::class, 'showForgetPassword'])
    ->name('password.request');
Route::post('/forget', [AuthController::class, 'sendResetLink'])
    ->name('password.email');
Route::get('/password/{token}', [AuthController::class, 'showResetPassword'])
    ->name('password.reset');
Route::post('/password', [AuthController::class, 'resetPassword'])
    ->name('password.update');

// créer un user
Route::get('/sign', [AuthController::class, 'register'])->name('sign');
Route::post('/sign', [AuthController::class, 'store'])->name('sign.store');


Route::get('/look', function () {
    return view('auth.look');
})->name('look');

Route::get('/password', function () {
    return view('auth.password');
})->name('password');


Route::get('/users', function () {
    return view('administration.user');
});

Route::get('/server', function () {
    return view('administration.serveur');
});

Route::get('/appli', function () {
    return view('administration.appli');
});
Route::get('/agent', function () {
    return view('administration.agent');
});

Route::get('/web', function () {
    return view('administration.webh');
});