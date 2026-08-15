<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Les colonnes que Laravel a le droit de modifier
    protected $fillable = [
        'name', 'email', 'telephone', 'role', 'department', 'password', 'status'];

    // Ce qui est caché quand on affiche l'utilisateur
    protected $hidden = [
        'password',
    ];
}
