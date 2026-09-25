<?php

namespace App\Providers;

use App\Models\Alert;
use App\Models\AuditLog;
use App\Models\Application;
use App\Models\Connector;
use App\Models\Server;
use App\Observers\ConnectorObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Observers\AlertObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Observer pour lier les Logs aux Alertes
        \App\Models\Log::observe(\App\Observers\LogObserver::class);

        View::composer('layout.sidebar', function ($view) {
            $alertant = Alert::whereNotIn('status', ['resolved', 'closed'])->count();
            $view->with('alertant', $alertant);
        });
         \App\Models\Alert::observe(\App\Observers\AlertObserver::class);
        Connector::observe(ConnectorObserver::class);
        Gate::policy(Connector::class, \App\Policies\ConnectorPolicy::class);

        // SYSTÈME D'AUDIT GLOBAL (Capture TOUT : Serveurs, Apps, Alertes, Settings...)
        Model::created(function ($model) {
            $this->logAction('CREATE', $model, "A créé : " . $this->getModelName($model));
        });

        Model::updated(function ($model) {
            // 1. On log l'action dans l'audit
            $this->logAction('UPDATE', $model, "A modifié : " . $this->getModelName($model));

            // 2. SYSTÈME D'ALERTE GLOBAL
            // Si un Serveur passe en état "critical"
            if ($model instanceof Server) {
                if ($model->isDirty('global_status') && $model->global_status === 'critical') {
                    Alert::firstOrCreate(
                        ['title' => 'Serveur en état critique : ' . $model->name, 'status' => 'open'],
                        [
                            'description' => 'Le serveur ' . $model->name . ' est passé en état CRITICAL.',
                            'source' => 'server_monitoring',
                            'priority' => 'critical',
                            'code' => 'SRV-' . $model->id
                        ]
                    );
                }
            }
            
            // Si une Application passe en état "suspended" ou "maintenance"
            if ($model instanceof Application) {
                if ($model->isDirty('status') && in_array($model->status, ['suspended', 'maintenance'])) {
                    Alert::firstOrCreate(
                        ['title' => 'Application indisponible : ' . $model->name, 'status' => 'open'],
                        [
                            'description' => 'L\'application ' . $model->name . ' est passée en statut : ' . strtoupper($model->status),
                            'source' => 'app_monitoring',
                            'priority' => 'critical',
                            'code' => 'APP-' . $model->id
                        ]
                    );
                }
            }
        });

        Model::deleted(function ($model) {
            $this->logAction('DELETE', $model, "A supprimé : " . $this->getModelName($model));
        });
        Alert::observe(AlertObserver::class);
    }

    protected function logAction(string $action, $model, string $details = null)
    {
        // On ignore l'enregistrement des logs dans les logs pour éviter une boucle infinie
        if ($model instanceof AuditLog) {
            return;
        }

        AuditLog::create([
            'user_id'       => Auth::id(),
            'action'        => $action,
            'resource_type' => class_basename($model),
            'resource_id'   => $model->id ?? null,
            'ip_address'    => Request::ip(),
            'is_success'    => true,
            'details'       => $details,
        ]);
    }

    protected function getModelName($model)
    {
        if (isset($model->name)) {
            return $model->name;
        }
        if (isset($model->title)) {
            return $model->title;
        }
        return 'ID: ' . $model->id;
    }
}