<?php

return [

    'backup' => [
        /*
         * El nombre de esta aplicación. Puedes usar este nombre para monitorear
         * los backups.
         */
        'name' => env('APP_NAME', 'laravel-backup'),

        'source' => [
            'files' => [
                /*
                 * Lista de directorios y archivos que serán incluidos en el backup.
                 * Solo archivos importantes para mantener el backup ligero.
                 */
                'include' => [
                    storage_path('app'),
                    public_path('uploads'),
                    public_path('logo_costeno_LP.svg'),
                    base_path('.env'),
                ],

                /*
                 * Estos directorios y archivos serán excluidos del backup.
                 * Los directorios usados por el proceso de backup se excluyen automáticamente.
                 */
                'exclude' => [
                    storage_path('app/backups'),
                    storage_path('framework'),
                    storage_path('logs'),
                    storage_path('app/backup-temp'),
                    public_path('css'),
                    public_path('js'),
                ],

                /*
                 * Determina si los symlinks deben ser seguidos.
                 */
                'follow_links' => false,

                /*
                 * Determina si debe evitar carpetas no leíbles.
                 */
                'ignore_unreadable_directories' => false,

                /*
                 * Esta ruta se usa para hacer directorios en el archivo zip relativos
                 * Establecer en `null` para incluir la ruta absoluta completa
                 */
                'relative_path' => null,
            ],

            /*
             * Los nombres de las conexiones a las bases de datos que deben ser respaldadas
             * MySQL, PostgreSQL, SQLite y Mongo databases son soportadas.
             */
            'databases' => [
                env('DB_CONNECTION', 'mysql'),
            ],
        ],

        /*
         * El dump de la base de datos puede ser comprimido para disminuir uso de disco.
         */
        'database_dump_compressor' => null,

        /*
         * Si se especifica, el nombre del archivo de dump incluirá un timestamp.
         */
        'database_dump_file_timestamp_format' => null,

        /*
         * La base del nombre del dump, ya sea 'database' o 'connection'
         */
        'database_dump_filename_base' => 'database',

        /*
         * La extensión del archivo usado para los dumps de base de datos.
         */
        'database_dump_file_extension' => '',

        'destination' => [
            /*
             * El algoritmo de compresión usado para crear el archivo zip.
             */
            'compression_method' => ZipArchive::CM_DEFAULT,

            /*
             * El nivel de compresión; un entero entre 0 y 9.
             */
            'compression_level' => 9,

            /*
             * El prefijo del nombre de archivo usado para el archivo zip de backup.
             */
            'filename_prefix' => '',

            /*
             * Los nombres de los discos donde los backups serán almacenados.
             * SOLO LOCAL - No servicios externos configurados
             */
            'disks' => [
                'local',
            ],
        ],

        /*
         * El directorio donde los archivos temporales serán almacenados.
         */
        'temporary_directory' => storage_path('app/backup-temp'),

        /*
         * La contraseña para encriptación del archivo.
         * Establecer en `null` para deshabilitar encriptación.
         */
        'password' => env('BACKUP_ARCHIVE_PASSWORD'),

        /*
         * El algoritmo de encriptación para encriptación del archivo.
         */
        'encryption' => 'default',

        /*
         * El número de intentos en caso que el comando de backup encuentre una excepción
         */
        'tries' => 1,

        /*
         * El número de segundos a esperar antes de intentar un nuevo backup si el previo falló
         */
        'retry_delay' => 0,
    ],

    /*
     * Puedes recibir notificaciones cuando ocurran eventos específicos.
     */
    'notifications' => [
        'notifications' => [
            \Spatie\Backup\Notifications\Notifications\BackupHasFailedNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFoundNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\CleanupHasFailedNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\BackupWasSuccessfulNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\HealthyBackupWasFoundNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\CleanupWasSuccessfulNotification::class => ['mail'],
        ],

        /*
         * Aquí puedes especificar el notifiable al cual las notificaciones deben ser enviadas.
         */
        'notifiable' => \Spatie\Backup\Notifications\Notifiable::class,

        'mail' => [
            'to' => env('BACKUP_NOTIFICATION_EMAIL', 'admin@tu-dominio.com'),

            'from' => [
                'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
                'name' => env('MAIL_FROM_NAME', 'Example'),
            ],
        ],

        'slack' => [
            'webhook_url' => '',
            'channel' => null,
            'username' => null,
            'icon' => null,
        ],

        'discord' => [
            'webhook_url' => '',
            'username' => '',
            'avatar_url' => '',
        ],
    ],

    /*
     * Aquí puedes especificar qué backups deben ser monitoreados.
     * Si un backup no cumple los requisitos especificados, el evento
     * UnHealthyBackupWasFound será disparado.
     */
    'monitor_backups' => [
        [
            'name' => env('APP_NAME', 'laravel-backup'),
            'disks' => ['local'],
            'health_checks' => [
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class => 1,
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class => 5000,
            ],
        ],
    ],

    'cleanup' => [
        /*
         * La estrategia que será usada para limpiar backups antiguos.
         * La estrategia por defecto mantendrá todos los backups por cierta cantidad de días.
         * Después de ese período solo un backup diario será mantenido.
         * Después solo backups semanales y así sucesivamente.
         */
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,

        'default_strategy' => [
            /*
             * El número de días por los cuales los backups deben ser mantenidos.
             */
            'keep_all_backups_for_days' => 7,

            /*
             * El backup más reciente de ese día será mantenido.
             */
            'keep_daily_backups_for_days' => 16,

            /*
             * El backup más reciente de esa semana será mantenido.
             */
            'keep_weekly_backups_for_weeks' => 8,

            /*
             * El backup más reciente de ese mes será mantenido.
             */
            'keep_monthly_backups_for_months' => 4,

            /*
             * El backup más reciente de ese año será mantenido.
             */
            'keep_yearly_backups_for_years' => 2,

            /*
             * Después de limpiar los backups, eliminar el backup más antiguo hasta
             * que esta cantidad de megabytes haya sido alcanzada.
             * Establecer en null para tamaño ilimitado.
             */
            'delete_oldest_backups_when_using_more_megabytes_than' => 5000,
        ],

        /*
         * El número de intentos en caso que el comando de cleanup encuentre una excepción
         */
        'tries' => 1,

        /*
         * El número de segundos a esperar antes de intentar una nueva limpieza
         */
        'retry_delay' => 0,
    ],

];
