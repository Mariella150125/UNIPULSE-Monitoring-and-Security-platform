<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwaspCategory extends Model
{
    protected $table = 'owasp_categories';

    protected $fillable = [
        'code',
        'name',
        'description',
        'url',
        'is_active',
    ];
}