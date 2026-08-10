<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class usere extends Model
{
    protected $table ='users';
    protected $fillable = ['name', 'email', 'telephone', 'email_verified_at', 'role', 'department','password','status'];
}
