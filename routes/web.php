<?php

use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/sign', function () {
    return view('auth.sign');
})->name('sign');

Route::get('/forget', function () {
    return view('auth.forget');
})->name('forget');

Route::get('/look', function () {
    return view('auth.look');
})->name('look');

Route::get('/password', function () {
    return view('auth.password');
})->name('password');

Route::get('/dashboard', function () {
    return view('layout.sidebar');
});

Route::get('/top', function () {
    return view('layout.topbar');
});

Route::get('/content', function () {
    return view('layout.dashboard');
});

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
