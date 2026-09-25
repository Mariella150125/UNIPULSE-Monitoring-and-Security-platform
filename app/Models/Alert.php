<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'title', 'description', 'resource_type', 'resource_id',
        'source', 'correlated_events', 'priority', 'status',
        'assigned_to', 'resolution_comment', 'last_escalated_at', 'snoozed_until'
    ];

    // Pour que Laravel comprenne que ce sont des tableaux (JSON)
    protected $casts = [
        'correlated_events' => 'array',
        'last_escalated_at' => 'datetime',
        'snoozed_until' => 'datetime',
    ];

    // Relation : Une alerte peut être assignée à un utilisateur
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Polymorphique : Pour lier l'alerte à un Serveur ou une Application
    public function resource()
    {
        return $this->morphTo();
    }
    public function comments()
    {
        return $this->hasMany(\App\Models\AlertComment::class)->latest();
    }
}