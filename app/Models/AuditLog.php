<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class AuditLog extends Model
{
  
    protected $fillable = [
        'user_id',
        'action',
        'resource_type',
        'resource_id',
        'ip_address',
        'is_success',
        'details',
    ];

    // Relation pour récupérer facilement l'utilisateur qui a fait l'action
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}