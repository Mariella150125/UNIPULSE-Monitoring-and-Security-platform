<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\AuditsActivity;

class Webhook extends Model
{
    use AuditsActivity;
    use SoftDeletes;
    
    protected $table = 'webhooks';

    public $timestamps = false;

    protected $fillable = [
        'direction', 'scope', 'name', 'connector_id', 'application_id',
        'target_url', 'auth_method', 'secret_hash', 'api_key_id',
        'min_severity_level', 'status', 'last_status', 'last_delivery_at', 'created_by', 'created_at',
    ];

    protected $casts = [
        'last_delivery_at' => 'datetime',
        'created_at'       => 'datetime',
    ];

    // --- Relations ---

    public function connector(): BelongsTo
    {
        return $this->belongsTo(Connector::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function authKey(): BelongsTo
    {
        return $this->belongsTo(ApiKey::class, 'api_key_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function eventTypes(): BelongsToMany
    {
        return $this->belongsToMany(
            WebhookEventType::class,
            'webhook_subscriptions',
            'webhook_id',
            'event_type_id'
        )->withPivot('created_at');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class);
    }

    public function secretsHistory(): HasMany
    {
        return $this->hasMany(WebhookSecretsHistory::class);
    }

    // --- Utilitaires ---

    public function rotateSecret(int $rotatedBy, ?string $reason = null): string
    {
         $preHash      = Str::random(48);
         $sharedSecret = hash('sha256', $preHash); // ← c'est ÇA le secret partagé

        $this->secretsHistory()->create([
            'rotated_by' => $rotatedBy,
            'reason'     => $reason,
        ]);

        $this->update(['secret_hash' => $sharedSecret]);

        return $sharedSecret;
    }
    // Dans app/Models/Webhook.php, ajouter :

    public function lastDelivery(): HasOne
    {
        return $this->hasOne(WebhookDelivery::class)->latestOfMany('delivered_at');
    }

}
