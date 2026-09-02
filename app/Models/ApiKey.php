<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Traits\AuditsActivity;

class ApiKey extends Model
{
    use AuditsActivity;
    protected $table = 'api_keys';

    protected $fillable = [
        'name', 'key_prefix', 'key_hash', 'user_id', 'status',
        'expires_at', 'last_used_at', 'last_used_ip', 'revoked_at',
    ];

    protected $casts = [
        'expires_at'    => 'datetime',
        'last_used_at'  => 'datetime',
        'revoked_at'    => 'datetime',
    ];

    // --- Relations ---

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopes(): HasMany
    {
        return $this->hasMany(ApiKeyScope::class);
    }

    public function requestLogs(): HasMany
    {
        return $this->hasMany(ApiRequestLog::class);
    }

    // --- Utilitaires ---

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function getIsUsableAttribute(): bool
    {
        return $this->status === 'active' && !$this->is_expired;
    }

    /**
     * Génère une clé API, retourne [ApiKey, $plainKey].
     * Le plain key n'est renvoyé qu'une seule fois.
     */
    public static function generate(string $name, int $userId, ?string $expiresAt = null): array
    {
        $plainKey = 'sk_live_' . Str::random(32);
        $prefix   = substr($plainKey, 0, 8);
        $hash     = hash('sha256', $plainKey);

        $key = static::create([
            'name'      => $name,
            'key_prefix'=> $prefix,
            'key_hash'  => $hash,
            'user_id'   => $userId,
            'expires_at'=> $expiresAt ? now()->parse($expiresAt) : null,
        ]);

        return [$key, $plainKey];
    }

    /**
     * Vérifie une clé brute et retourne l'enregistrement si valide et utilisable.
     */
    public static function authenticate(string $plainKey): ?self
    {
        $hash = hash('sha256', $plainKey);
        $key  = static::where('key_hash', $hash)->first();

        if (!$key || !$key->is_usable) {
            return null;
        }

        return $key;
    }

    public function suspend(): void
    {
        if ($this->status === 'active') {
            $this->update(['status' => 'suspended']);
        }
    }

    public function resume(): void
    {
        if ($this->status === 'suspended' && !$this->is_expired) {
            $this->update(['status' => 'active']);
        }
    }

    public function revoke(): void
    {
        $this->update([
            'status'     => 'revoked',
            'revoked_at' => now(),
        ]);
    }

    public function regenerate(): string
    {
        $plainKey = 'sk_live_' . Str::random(32);
        $this->update([
            'key_prefix' => substr($plainKey, 0, 8),
            'key_hash'   => hash('sha256', $plainKey),
        ]);
        return $plainKey;
    }

    /**
     * Formate les scopes pour l'affichage : ["servers:read", "alerts:write"]
     */
    public function getScopeListAttribute(): array
    {
        return $this->scopes->map(fn ($s) => "{$s->resource}:{$s->action}")->toArray();
    }
}