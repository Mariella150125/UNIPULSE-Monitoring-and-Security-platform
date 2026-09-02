<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\AuditsActivity;

class Server extends Model
{
    use AuditsActivity;
    use HasFactory;

    protected $fillable = [
        'name',
        'hostname',
        'ip_address',
        'port',
        'os',
        'os_version',
        'environment',
        'department',
        'description',
        'tags',
        'group_id',
        'prometheus_instance',
        'prometheus_job',
        'prometheus_reachable',
        'wazuh_agent_id',
        'wazuh_group',
        'wazuh_agent_status',
    ];

    protected $casts = [
        'tags' => 'array',
        'prometheus_reachable' => 'boolean',
        'global_status_updated_at' => 'datetime',
    ];
    public function group()
    {
        return $this->belongsTo(ServerGroup::class, 'group_id');
    }
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }
}
