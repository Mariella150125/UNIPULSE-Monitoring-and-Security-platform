<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes; 
use App\Traits\AuditsActivity;

class ApplicationEndpoint extends Model
{
    use AuditsActivity; 
    use SoftDeletes; 

    protected $table = 'application_endpoints';

    public $timestamps = false;

    protected $fillable = [
        'application_id', 'url', 'http_method', 'auth_headers',
        'frequency_seconds', 'last_status', 'last_response_time_ms', 'last_checked_at',
    ];

    protected $casts = [
        'last_checked_at' => 'datetime',
        'auth_headers'         => 'encrypted', 
        'is_enabled'      => 'boolean', 
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
