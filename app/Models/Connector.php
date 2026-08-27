<?php

namespace App\Models;

use App\Observers\ConnectorObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

#[ObservedBy(ConnectorObserver::class)]
class Connector extends Model
{
    use HasFactory;

    // ── Casts automatiques ──
    protected $casts = [
        'extra_config'    => 'array',
        'api_port'        => 'integer',
        'last_check_at'   => 'datetime',
        'last_success_at' => 'datetime',
    ];

    // ── Champs masqués dans les sorties JSON ──
    protected $hidden = [
        'auth_password_encrypted',
    ];

    // ── Champs assignables en masse ──
    protected $fillable = [
        'type',
        'name',
        'base_url',
        'auth_username',
        'auth_password_encrypted',
        'api_port',
        'extra_config',
        'status',
        'last_check_at',
        'last_success_at',
        'last_error_message',
        'created_by',
        'updated_by',
    ];

    // ──────────────────────────────────────────
    //  RELATIONS
    // ──────────────────────────────────────────

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function logs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ConnectorLog::class)->orderByDesc('executed_at');
    }

    public function recentLogs(int $limit = 20)
    {
        return $this->logs()->limit($limit)->get();
    }

    public function getIsProlongedFailureAttribute(): bool
    {
        if ($this->status !== 'error') {
            return false;
        }

        if ($this->last_success_at === null) {
            return $this->last_check_at !== null
                && $this->last_check_at->lt(now()->subMinutes(30));
        }

        return $this->last_success_at->lt(now()->subMinutes(30));
    }

    // ──────────────────────────────────────────
    //  CHIFFREMENT / DÉCHIFFREMENT
    // ──────────────────────────────────────────

    public function encryptPassword(?string $plainPassword): self
    {
        if ($plainPassword !== null && $plainPassword !== '') {
            $this->auth_password_encrypted = Crypt::encryptString($plainPassword);
        }

        return $this;
    }

    public function decryptPassword(): ?string
    {
        if (empty($this->auth_password_encrypted)) {
            return null;
        }

        try {
            return Crypt::decryptString($this->auth_password_encrypted);
        } catch (\Throwable $e) {
            return null;
        }
    }

    // ──────────────────────────────────────────
    //  ACCESSORS
    // ──────────────────────────────────────────

    public function getHasPasswordAttribute(): bool
    {
        return ! empty($this->auth_password_encrypted);
    }

    public function getEffectivePortAttribute(): int
    {
        if ($this->api_port !== null) {
            return $this->api_port;
        }

        $parsed = parse_url($this->base_url);
        return (int) ($parsed['port'] ?? ($parsed['scheme'] === 'https' ? 443 : 80));
    }

    public function getFullUrlAttribute(): string
    {
        $parsed = parse_url($this->base_url);
        $scheme = $parsed['scheme'] ?? 'https';
        $host   = $parsed['host'] ?? '';
        $path   = $parsed['path'] ?? '';

        return "{$scheme}://{$host}:{$this->effective_port}{$path}";
    }

    // ──────────────────────────────────────────
    //  HELPERS STATUT
    // ──────────────────────────────────────────

    public function markAsConnected(): void
    {
        $this->update([
            'status'             => 'connected',
            'last_check_at'     => now(),
            'last_success_at'   => now(),
            'last_error_message' => null,
        ]);
    }

    public function markAsError(string $message): void
    {
        $this->update([
            'status'             => 'error',
            'last_check_at'      => now(),
            'last_error_message' => $message,
        ]);
    }

    public function markAsNeverTested(): void
    {
        $this->update([
            'status'             => 'never_tested',
            'last_check_at'      => null,
            'last_success_at'    => null,
            'last_error_message' => null,
        ]);
    }
}