<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\AuditsActivity;

class ApiKeyScope extends Model
{
    use AuditsActivity; 
    protected $table = 'api_key_scopes';

    protected $fillable = ['api_key_id', 'resource', 'action'];

    public $timestamps = false;

    public function apiKey(): BelongsTo
    {
        return $this->belongsTo(ApiKey::class);
    }
}
