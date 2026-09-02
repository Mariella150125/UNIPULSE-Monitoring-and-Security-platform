<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait AuditsActivity
{
    // 1. PARTIE AUTOMATIQUE : S'exécute automatiquement quand un Modèle est créé, modifié ou supprimé
        public static function bootAuditsActivity()
    {
        // Évite de planter si l'action est faite via la ligne de commande
        if (app()->runningInConsole()) {
            return;
        }

        // SÉCURITÉ ANTI-BOUCLE : On ne loggera jamais le modèle AuditLog
        static::created(function ($model) {
            if ($model instanceof \App\Models\AuditLog) return;
            self::createAutoLog('create', $model);
        });

        static::updated(function ($model) {
            if ($model instanceof \App\Models\AuditLog) return;
            self::createAutoLog('update', $model);
        });

        static::deleted(function ($model) {
            if ($model instanceof \App\Models\AuditLog) return;
            self::createAutoLog('delete', $model);
        });
    }

    // Fonction interne pour formatter le log automatique
    protected static function createAutoLog(string $action, $model)
    {
        AuditLog::create([
            'user_id'      => Auth::id(),
            'action'       => $action . '_' . strtolower(class_basename($model)), // ex: create_servergroup
            'resource_type'=> class_basename($model), // ex: ServerGroup
            'resource_id'  => $model->id,
            'ip_address'   => request()->ip(),
            'is_success'   => true,
            'details'      => "Action automatique sur " . class_basename($model) . " (ID: {$model->id})",
        ]);
    }

    // 2. PARTIE MANUELLE : Ta fonction d'origine pour les contrôleurs
    protected function logAudit(string $action, string $resourceType, $resourceId = null, bool $isSuccess = true, ?string $details = null)
    {
        AuditLog::create([
            'user_id'      => Auth::id(),
            'action'       => $action,
            'resource_type'=> $resourceType,
            'resource_id'  => $resourceId,
            'ip_address'   => request()->ip(),
            'is_success'   => $isSuccess,
            'details'      => $details,
        ]);
    }
}