<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\AuditsActivity;

class ApiRequestLog extends Model
{
    use AuditsActivity; 
    protected $table = 'api_request_logs';

    public $timestamps = false;

    protected $fillable = [
        'api_key_id', 'endpoint', 'method', 'status_code',
        'ip_address', 'response_time_ms', 'requested_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
    ];

    public function apiKey(): BelongsTo
    {
        return $this->belongsTo(ApiKey::class);
    }
}
