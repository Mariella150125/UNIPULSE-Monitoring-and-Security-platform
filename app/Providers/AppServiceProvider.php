<?php

namespace App\Providers;

use App\Models\Alert;
use App\Models\AuditLog;
use App\Models\Connector;
use App\Observers\ConnectorObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        View::composer('layout.sidebar', function ($view) {
            $alertant = Alert::whereNotIn('status', ['resolved', 'closed'])->count();
            $view->with('alertant', $alertant);
        });

        Connector::observe(ConnectorObserver::class);
        Gate::policy(Connector::class, \App\Policies\ConnectorPolicy::class);

        // SYSTÈME D'AUDIT GLOBAL (Capture TOUT : Serveurs, Apps, Alertes, Settings...)
        Model::created(function ($model) {
            $this->logAction('CREATE', $model, "A créé : " . $this->getModelName($model));
        });

        Model::updated(function ($model) {
            $this->logAction('UPDATE', $model, "A modifié : " . $this->getModelName($model));
        });

        Model::deleted(function ($model) {
            $this->logAction('DELETE', $model, "A supprimé : " . $this->getModelName($model));
        });
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
            'resource_type' => class_basename($model), // Ex: "Server", "Alert"
            'resource_id'   => $model->id ?? null,
            'ip_address'    => Request::ip(),
            'is_success'    => true,
            'details'       => $details,
        ]);
    }

    protected function getModelName($model)
    {
        // Si le modèle a un champ "name" ou "title", on l'utilise
        if (isset($model->name)) {
            return $model->name;
        }
        if (isset($model->title)) {
            return $model->title;
        }
        // Sinon on retourne l'ID
        return 'ID: ' . $model->id;
    }
}