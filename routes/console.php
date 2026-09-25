<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
Schedule::command('reports:send daily')->dailyAt('08:00');

// Envoyer le rapport chaque lundi à 08h00 du matin
Schedule::command('reports:send weekly')->weeklyOn(1, '08:00');

Schedule::command('monitor:run')->everyMinute()
    ->withoutOverlapping() // Empêche de lancer un 2ème cycle si le 1er n'est pas terminé
    ->appendOutputTo(storage_path('logs/monitor.log')); // Sauvegarde les erreurs dans un log