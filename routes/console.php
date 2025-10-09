<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Programar backups automáticos
 * Los backups se guardan en storage/app/backups
 */

// Ejecutar backup completo diariamente a las 2:00 AM
Schedule::command('backup:run --only-db')
    ->dailyAt('02:00')
    ->emailOutputOnFailure(env('BACKUP_NOTIFICATION_EMAIL', 'admin@tu-dominio.com'));

// Limpiar backups antiguos diariamente a las 1:00 AM
Schedule::command('backup:clean')
    ->dailyAt('01:00')
    ->emailOutputOnFailure(env('BACKUP_NOTIFICATION_EMAIL', 'admin@tu-dominio.com'));

// Monitorear estado de backups diariamente a las 3:00 AM
Schedule::command('backup:monitor')
    ->dailyAt('03:00')
    ->emailOutputOnFailure(env('BACKUP_NOTIFICATION_EMAIL', 'admin@tu-dominio.com'));
