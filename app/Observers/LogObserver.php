<?php

namespace App\Observers;

use App\Models\Log;
use App\Models\Alert;

class LogObserver
{
    public function created(Log $log)
    {
        // On déclenche une alerte pour les logs ERROR et CRITICAL
        if (in_array($log->level, ['ERROR', 'CRITICAL'])) {
            Alert::firstOrCreate(
                [
                    'title' => 'Incident Applicatif (' . $log->level . ') : ' . substr($log->message, 0, 50), 
                    'status' => 'open'
                ],
                [
                    'description' => 'Source : ' . $log->source . ' | App : ' . ($log->application?->name ?? 'N/A') . ' | Message : ' . $log->message,
                    'source' => 'log_ingest',
                    'priority' => 'critical',
                    'code' => 'LOG-' . $log->id
                ]
            );
        }
    }
}