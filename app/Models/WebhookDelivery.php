<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\AuditsActivity;

class WebhookDelivery extends Model
{
    use AuditsActivity;
    protected $table = 'webhook_deliveries';

    public $timestamps = false;

    protected $fillable = [
        'webhook_id', 'event_type_id', 'direction', 'attempt_number',
        'payload', 'signature_valid', 'http_status', 'success',
        'error_message', 'duration_ms', 'delivered_at',
    ];

    protected $casts = [
        'payload'         => 'array',
        'signature_valid' => 'boolean',
        'success'         => 'boolean',
        'delivered_at'    => 'datetime',
    ];

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class);
    }

    public function eventType(): BelongsTo
    {
        return $this->belongsTo(WebhookEventType::class);
    }
}