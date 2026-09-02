<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\AuditsActivity;

class WebhookEventType extends Model
{
    use AuditsActivity;
    protected $table = 'webhook_event_types';

    public $timestamps = false;

    protected $fillable = ['code', 'label', 'applicable_direction', 'description'];

    public function webhooks(): BelongsToMany
    {
        return $this->belongsToMany(
            Webhook::class,
            'webhook_subscriptions',
            'event_type_id',
            'webhook_id'
        )->withPivot('created_at');
    }
}
