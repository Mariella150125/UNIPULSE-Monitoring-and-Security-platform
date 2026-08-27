
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ApplicationTypeController;
use App\Http\Controllers\ServerGroupController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\ConnectorController;
use Illuminate\Http\Request;


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

// gestion des serveurs 

Route::resource('server', ServerController::class)
    ->except(['create']);

// applications 
Route::resource('appli', ApplicationController::class)
    ->only(['index', 'store', 'update', 'destroy']);
Route::resource(
    'application-types',
    ApplicationTypeController::class
)->except(['create', 'show', 'edit']);

Route::get(
    '/search',
    [GlobalSearchController::class, 'index']
)->name('global.search');

Route::get('/profile', function () {
    return view('layout.profile');
})->name('profile');

Route::get('/settings', function () {
    return view('layout.settings');
})->name('settings');

Route::resource(
    'server-groups',
    ServerGroupController::class
)->only([
    'index',
    'store',
    'update',
]);


Route::get('/language/{locale}', function ($locale) {

    if (!in_array($locale, ['fr', 'en'])) {
        abort(404);
    }

    session(['locale' => $locale]);

    return redirect()->back();

})->name('language');

Route::post('/language/change', function (Request $request) {

    $language = $request->input('language');

    if (in_array($language, ['fr', 'en'])) {
        session(['locale' => $language]);
    }

    return back();

})->name('language.change');



Route::middleware(['auth'])->group(function () {
    Route::get('/connecteurs', [ConnectorController::class, 'index'])->name('connectors.index');
    Route::post('/connecteurs', [ConnectorController::class, 'store'])->name('connectors.store');
    Route::get('/connecteurs/{connector}/show', [ConnectorController::class, 'show'])->name('connectors.show');
    Route::get('/connecteurs/{connector}/edit', [ConnectorController::class, 'edit'])->name('connectors.edit');
    Route::put('/connecteurs/{connector}', [ConnectorController::class, 'update'])->name('connectors.update');
    Route::get('/connecteurs/{connector}/delete', [ConnectorController::class, 'delete'])->name('connectors.delete');
    Route::delete('/connecteurs/{connector}', [ConnectorController::class, 'destroy'])->name('connectors.destroy');
    Route::get('/connecteurs/{connector}/plug', [ConnectorController::class, 'plug'])->name('connectors.plug');
    Route::post('/connecteurs/{connector}/test', [ConnectorController::class, 'test'])->name('connectors.test')
        ->middleware('throttle.connector.test');
    Route::get('/connecteurs/{connector}/edit-data', [ConnectorController::class, 'editData'])->name('connectors.edit-data');
    Route::post('/connecteurs/test-preview', [ConnectorController::class, 'testPreview'])->name('connectors.test-preview');
});




Route::get('/web', function () {
    return view('administration.webh');
});