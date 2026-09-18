<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceWindow extends Model
{
    protected $fillable = [
        'resource_type', 
        'resource_name', 
        'start_time', 
        'end_time', 
        'is_active'
    ];

    // Fonction magique pour savoir si une ressource est en maintenance MAINTENANT
    public static function isUnderMaintenance($resourceName)
    {
        $now = now();
        return self::where('resource_name', $resourceName)
            ->where('is_active', true)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->exists();
    }
}