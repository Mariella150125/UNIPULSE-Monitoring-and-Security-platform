<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $table = 'logs';

    // On dit à Laravel de ne pas gérer updated_at, seulement created_at
    const UPDATED_AT = null;

    protected $fillable = [
        'application_id',
        'source',
        'level',
        'message',
        'ip_address',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}