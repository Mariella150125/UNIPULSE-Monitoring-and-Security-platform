<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;

class User extends Authenticatable implements CanResetPassword
{
    use HasFactory, Notifiable, CanResetPasswordTrait;

    // Les colonnes que Laravel a le droit de modifier
    protected $fillable = [
        'name', 'email', 'telephone', 'role', 'department', 'password', 'status'];

    // Ce qui est caché quand on affiche l'utilisateur
    protected $hidden = [
        'password',
    ];
    protected $casts = [
        'last_login' => 'datetime',
    ];
}
