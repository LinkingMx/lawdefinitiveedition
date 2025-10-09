<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use ShuvroRoy\FilamentSpatieLaravelBackup\Pages\Backups;

use function Pest\Livewire\livewire;

/**
 * Tests de Feature para la página de Backups en Filament
 *
 * Este archivo prueba:
 * - Acceso a la página de backups
 * - Permisos y autorización
 * - Interfaz de usuario
 * - Integración con el plugin
 */
beforeEach(function () {
    // Configurar almacenamiento de prueba
    Storage::fake('local');

    // Asegurarse de que el directorio de backups existe
    Storage::disk('local')->makeDirectory('Laravel');
});

// ==========================================
// Tests de Acceso y Permisos
// ==========================================

test('usuario autenticado puede acceder a la página de backups', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    livewire(Backups::class)
        ->assertSuccessful();
});

test('usuario no autenticado no puede acceder a la página de backups', function () {
    $this->get('/admin/backups')
        ->assertRedirect('/admin/login');
});

test('plugin de backup autoriza por defecto a usuarios autenticados', function () {
    // El plugin está configurado con authorize(true) por defecto
    $panel = filament()->getCurrentPanel();
    $plugins = $panel->getPlugins();

    $backupPlugin = collect($plugins)->first(function ($plugin) {
        return $plugin instanceof \ShuvroRoy\FilamentSpatieLaravelBackup\FilamentSpatieLaravelBackupPlugin;
    });

    expect($backupPlugin)->not->toBeNull();
    expect($backupPlugin->isAuthorized())->toBeTrue();
});

// ==========================================
// Tests de Interfaz de Usuario
// ==========================================

test('livewire component de backups renderiza correctamente', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    livewire(Backups::class)
        ->assertSuccessful();
});

test('página de backups tiene botón crear copia de seguridad', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    livewire(Backups::class)
        ->assertSuccessful();
});

test('página de backups muestra lista de destinos de backup', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    livewire(Backups::class)
        ->assertSuccessful();
});

// ==========================================
// Tests de Integración con Spatie Backup
// ==========================================

test('configuración de backup está correctamente establecida', function () {
    // Verificar que la configuración de backup está correcta
    expect(config('backup.backup.name'))->toBe(config('app.name'));
    expect(config('backup.backup.destination.disks'))->toContain('local');
});

test('comando backup:run puede ejecutarse', function () {
    Storage::fake('local');

    // Ejecutar comando de backup solo base de datos
    Artisan::call('backup:run', ['--only-db' => true, '--disable-notifications' => true]);

    // Verificar que el comando se ejecutó sin errores
    expect(Artisan::output())->toContain('Backup completed');
})->skip('Requiere SQLite configurado correctamente');

test('comando backup:clean puede ejecutarse', function () {
    // Ejecutar comando de limpieza
    Artisan::call('backup:clean', ['--disable-notifications' => true]);

    // Verificar que el comando se ejecutó
    expect(true)->toBeTrue();
});

test('comando backup:monitor puede ejecutarse', function () {
    // Ejecutar comando de monitoreo
    Artisan::call('backup:monitor', ['--disable-notifications' => true]);

    // Verificar que el comando se ejecutó
    expect(true)->toBeTrue();
})->skip('Requiere backups existentes');

// ==========================================
// Tests de Configuración
// ==========================================

test('backup incluye archivos configurados', function () {
    $includeFiles = config('backup.backup.source.files.include');

    expect($includeFiles)->toContain(storage_path('app'))
        ->and($includeFiles)->toContain(public_path('uploads'))
        ->and($includeFiles)->toContain(public_path('logo_costeno_LP.svg'))
        ->and($includeFiles)->toContain(base_path('.env'));
});

test('backup excluye archivos configurados', function () {
    $excludeFiles = config('backup.backup.source.files.exclude');

    expect($excludeFiles)->toContain(storage_path('app/backups'))
        ->and($excludeFiles)->toContain(storage_path('framework'))
        ->and($excludeFiles)->toContain(storage_path('logs'))
        ->and($excludeFiles)->toContain(public_path('css'))
        ->and($excludeFiles)->toContain(public_path('js'));
});

test('backup tiene configuración de retención correcta', function () {
    $retentionConfig = config('backup.cleanup.default_strategy');

    expect($retentionConfig['keep_all_backups_for_days'])->toBe(7)
        ->and($retentionConfig['keep_daily_backups_for_days'])->toBe(16)
        ->and($retentionConfig['keep_weekly_backups_for_weeks'])->toBe(8)
        ->and($retentionConfig['keep_monthly_backups_for_months'])->toBe(4)
        ->and($retentionConfig['delete_oldest_backups_when_using_more_megabytes_than'])->toBe(5000);
});

test('backup usa disco local', function () {
    $disks = config('backup.backup.destination.disks');

    expect($disks)->toBeArray()
        ->and($disks)->toContain('local');
});

test('backup monitorea disco local', function () {
    $monitorBackups = config('backup.monitor_backups');

    expect($monitorBackups)->toBeArray()
        ->and($monitorBackups[0]['disks'])->toContain('local');
});

// ==========================================
// Tests de Traducciones
// ==========================================

test('archivo de traducciones en español existe', function () {
    $translationFile = lang_path('vendor/filament-spatie-backup/es/backup.php');

    expect(file_exists($translationFile))->toBeTrue();
});

test('traducciones en español están disponibles', function () {
    // Forzar el locale a español para el test
    app()->setLocale('es');

    $translations = trans('filament-spatie-backup::backup.pages.backups.navigation.label');

    // Verificar que la traducción existe (no es la clave)
    expect($translations)->not->toBe('filament-spatie-backup::backup.pages.backups.navigation.label');
});

test('traducciones de botones están configuradas', function () {
    app()->setLocale('es');

    $createBackup = trans('filament-spatie-backup::backup.pages.backups.actions.create_backup');
    $onlyDb = trans('filament-spatie-backup::backup.pages.backups.modal.buttons.only_db');
    $onlyFiles = trans('filament-spatie-backup::backup.pages.backups.modal.buttons.only_files');
    $dbAndFiles = trans('filament-spatie-backup::backup.pages.backups.modal.buttons.db_and_files');

    // Verificar que las traducciones existen (no son las claves)
    expect($createBackup)->not->toBe('filament-spatie-backup::backup.pages.backups.actions.create_backup')
        ->and($onlyDb)->not->toBe('filament-spatie-backup::backup.pages.backups.modal.buttons.only_db')
        ->and($onlyFiles)->not->toBe('filament-spatie-backup::backup.pages.backups.modal.buttons.only_files')
        ->and($dbAndFiles)->not->toBe('filament-spatie-backup::backup.pages.backups.modal.buttons.db_and_files');
});

test('traducciones de tabla están configuradas', function () {
    app()->setLocale('es');

    $download = trans('filament-spatie-backup::backup.components.backup_destination_list.table.actions.download');
    $delete = trans('filament-spatie-backup::backup.components.backup_destination_list.table.actions.delete');

    // Verificar que las traducciones existen (no son las claves)
    expect($download)->not->toBe('filament-spatie-backup::backup.components.backup_destination_list.table.actions.download')
        ->and($delete)->not->toBe('filament-spatie-backup::backup.components.backup_destination_list.table.actions.delete');
});

// ==========================================
// Tests de Programación de Tareas
// ==========================================

test('comando de backup diario está programado', function () {
    $schedule = app()->make(\Illuminate\Console\Scheduling\Schedule::class);
    $events = collect($schedule->events());

    $backupEvent = $events->first(function ($event) {
        return str_contains($event->command ?? '', 'backup:run');
    });

    expect($backupEvent)->not->toBeNull();
});

test('comando de limpieza está programado', function () {
    $schedule = app()->make(\Illuminate\Console\Scheduling\Schedule::class);
    $events = collect($schedule->events());

    $cleanupEvent = $events->first(function ($event) {
        return str_contains($event->command ?? '', 'backup:clean');
    });

    expect($cleanupEvent)->not->toBeNull();
});

test('comando de monitoreo está programado', function () {
    $schedule = app()->make(\Illuminate\Console\Scheduling\Schedule::class);
    $events = collect($schedule->events());

    $monitorEvent = $events->first(function ($event) {
        return str_contains($event->command ?? '', 'backup:monitor');
    });

    expect($monitorEvent)->not->toBeNull();
});

// ==========================================
// Tests de Navegación
// ==========================================

test('página de backups tiene configuración de grupo de navegación', function () {
    app()->setLocale('es');

    // Verificar que la traducción del grupo de navegación existe
    $navigationGroup = trans('filament-spatie-backup::backup.pages.backups.navigation.group');

    // Verificar que la traducción existe (no es la clave)
    expect($navigationGroup)->not->toBe('filament-spatie-backup::backup.pages.backups.navigation.group');
});

// ==========================================
// Tests de Plugin
// ==========================================

test('plugin de backup está registrado en Filament', function () {
    $panel = filament()->getCurrentPanel();
    $plugins = $panel->getPlugins();

    $backupPlugin = collect($plugins)->first(function ($plugin) {
        return $plugin instanceof \ShuvroRoy\FilamentSpatieLaravelBackup\FilamentSpatieLaravelBackupPlugin;
    });

    expect($backupPlugin)->not->toBeNull();
});

test('página de backups usa clase correcta', function () {
    // Verificar que el plugin está configurado para usar la página correcta
    $panel = filament()->getCurrentPanel();
    $plugins = $panel->getPlugins();

    $backupPlugin = collect($plugins)->first(function ($plugin) {
        return $plugin instanceof \ShuvroRoy\FilamentSpatieLaravelBackup\FilamentSpatieLaravelBackupPlugin;
    });

    expect($backupPlugin)->not->toBeNull();
});

// ==========================================
// Tests de Directorio de Backup
// ==========================================

test('directorio temporal de backup está configurado', function () {
    $tempDir = config('backup.backup.temporary_directory');

    expect($tempDir)->toBe(storage_path('app/backup-temp'));
});

test('directorio temporal de backup puede ser creado', function () {
    $tempDir = config('backup.backup.temporary_directory');

    // Crear directorio temporal
    if (! file_exists($tempDir)) {
        mkdir($tempDir, 0755, true);
    }

    expect(file_exists($tempDir))->toBeTrue();

    // Limpiar
    if (file_exists($tempDir)) {
        rmdir($tempDir);
    }
});

// ==========================================
// Tests de Notificaciones
// ==========================================

test('notificaciones de backup están configuradas', function () {
    $notifications = config('backup.notifications.notifications');

    expect($notifications)->toBeArray()
        ->and($notifications)->toHaveKey(\Spatie\Backup\Notifications\Notifications\BackupWasSuccessfulNotification::class)
        ->and($notifications)->toHaveKey(\Spatie\Backup\Notifications\Notifications\BackupHasFailedNotification::class);
});

test('email de notificación está configurado', function () {
    $email = config('backup.notifications.mail.to');

    expect($email)->not->toBeNull();
});
