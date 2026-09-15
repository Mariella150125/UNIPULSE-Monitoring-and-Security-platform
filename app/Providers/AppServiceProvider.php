<?php

namespace App\Providers;

use App\Models\Connector;
use App\Observers\ConnectorObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Database\Eloquent\Model;
use App\Models\Alert;
use App\Models\AuditLog;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // 1. Compteur d'alertes pour la sidebar
        View::composer('layout.sidebar', function ($view) {
            $alertant = Alert::whereNotIn('status', ['resolved', 'closed'])->count();
            $view->with('alertant', $alertant);
        });

        // 2. Observers et Policies pour les Connecteurs
        Connector::observe(ConnectorObserver::class);
        Gate::policy(Connector::class, \App\Policies\ConnectorPolicy::class);

        // 3. SYSTÈME D'AUDIT GLOBAL (Capture toutes les actions CRUD)
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

    // Fonction pour insérer la ligne dans la base de données
    protected function logAction(string $action, $model, string $details = null)
    {
        // On ignore l'enregistrement des logs dans les logs pour éviter une boucle infinie
        if ($model instanceof AuditLog) {
            return;
        }

        AuditLog::create([
            'user_id'       => Auth::id(),
            'action'        => $action,
            'resource_type' => class_basename($model), // Ex: "Server", "Application"
            'resource_id'   => $model->id ?? null,
            'ip_address'    => Request::ip(),
            'is_success'    => true,
            'details'       => $details,
        ]);
    }

    // Petite fonction pour trouver le nom de la ressource
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