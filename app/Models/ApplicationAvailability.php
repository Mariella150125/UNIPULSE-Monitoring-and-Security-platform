<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationAvailability extends Model
{
    protected $table = 'application_availability';

    protected $fillable = [
        'application_id',
        'checked_at',
        'is_available',
        'response_time',
        'status_code',
    ];

    protected $casts = [
        'checked_at' => 'datetime',
        'is_available' => 'boolean',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}