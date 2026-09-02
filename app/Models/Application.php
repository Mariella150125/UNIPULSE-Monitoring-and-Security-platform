<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\AuditsActivity;

class Application extends Model
{
    use AuditsActivity; 
    protected $fillable = [
        'name',
        'description',
        'url',
        'language',
        'framework',
        'version',

        // Classification
        'application_type_id',
        'environment',
        'database_used',

        // Informations métier
        'client_name',
        'tags',
        'status',
        'responsible_user_id',

        // Hébergement
        'is_hosted',
        'server_id',
        'port',
        'deployment_path',

        // Monitoring Prometheus
        'monitoring_enabled',
        'prometheus_job',
        'metrics_endpoint',
        'scrape_interval',
        'url_health_check',
        'last_sync_at',

        // Sécurité Wazuh
        'wazuh_enabled',

        // Criticité
        'criticality',
    ];

    protected $casts = [
        'is_hosted' => 'boolean',
        'monitoring_enabled' => 'boolean',
        'wazuh_enabled' => 'boolean',
        'tags' => 'array',
        'last_sync_at' => 'datetime',
    ];

    /**
     * Serveur sur lequel l'application est hébergée.
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * Utilisateur responsable de l'application.
     */
    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    /**
     * Type de l'application.
     */
    public function applicationType(): BelongsTo
    {
        return $this->belongsTo(ApplicationType::class);
    }
}