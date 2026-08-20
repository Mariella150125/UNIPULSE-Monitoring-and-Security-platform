
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;


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
Route::post('/resend-welcome', [AuthController::class, 'resendWelcomeLink'])->name('resend.welcome');
// créer un user
Route::get('/sign', [AuthController::class, 'register'])->name('sign');
Route::post('/sign', [AuthController::class, 'store'])->name('sign.store');


Route::get('/look', function () {
    return view('auth.look');
})->name('look');

Route::get('/password', function () {
    return view('auth.password');
})->name('password');

// CRUD USERS

Route::get('/users', [UserController::class, 'index'])
    ->middleware('auth')
    ->name('users');

Route::get('/users/{id}', [UserController::class, 'show'])
    ->middleware('auth')
    ->name('users.show');

Route::get('/users/{id}/edit', [UserController::class, 'edit'])
    ->middleware('auth')
    ->name('users.edit');

Route::put('/users/{id}', [UserController::class, 'update'])
    ->middleware('auth')
    ->name('users.update');

Route::get('/users/{id}/delete', [UserController::class, 'delete'])
    ->middleware('auth')
    ->name('users.delete');

Route::delete('/users/{id}', [UserController::class, 'destroy'])
    ->middleware('auth')
    ->name('users.destroy');

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