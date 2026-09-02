<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\AuditsActivity;

class WebhookSecretsHistory extends Model
{
    use AuditsActivity;
    protected $table = 'webhook_secrets_history';

    public $timestamps = false;

    protected $fillable = ['webhook_id', 'rotated_by', 'rotated_at', 'reason'];

    protected $casts = [
        'rotated_at' => 'datetime',
    ];

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class);
    }

    public function rotatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rotated_by');
    }
}
