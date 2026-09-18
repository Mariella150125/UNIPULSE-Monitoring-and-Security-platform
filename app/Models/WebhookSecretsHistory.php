<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookSecretsHistory extends Model
{
    protected $table = 'webhook_secrets_history';

    protected $fillable = [
        'webhook_id',
        'rotated_by',
        'reason',
        'rotated_at',
    ];

    public function rotatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rotated_by');
    }
}