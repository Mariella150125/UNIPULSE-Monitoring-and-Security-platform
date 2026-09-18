<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityScoreHistory extends Model
{
    protected $fillable = ['score', 'recorded_at'];
}