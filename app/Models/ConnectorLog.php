<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConnectorLog extends Model
{
    use HasFactory;

    protected $casts = [
        'executed_at' => 'datetime',
        'success'     => 'boolean',
        'duration_ms' => 'integer',
    ];

    protected $fillable = [
        'connector_id',
        'executed_at',
        'success',
        'duration_ms',
        'error_message',
    ];

    public function connector(): BelongsTo
    {
        return $this->belongsTo(Connector::class);
    }
}