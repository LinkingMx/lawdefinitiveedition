<?php

return [

    'components' => [
        'backup_destination_list' => [
            'table' => [
                'actions' => [
                    'download' => 'Descargar',
                    'delete' => 'Eliminar',
                ],

                'fields' => [
                    'path' => 'Ruta',
                    'disk' => 'Disco',
                    'date' => 'Fecha',
                    'size' => 'Tamaño',
                ],

                'filters' => [
                    'disk' => 'Disco',
                ],
            ],
        ],

        'backup_destination_status_list' => [
            'table' => [
                'fields' => [
                    'name' => 'Nombre',
                    'disk' => 'Disco',
                    'healthy' => 'Estado',
                    'amount' => 'Cantidad',
                    'newest' => 'Más Reciente',
                    'used_storage' => 'Espacio Utilizado',
                ],
            ],
        ],
    ],

    'pages' => [
        'backups' => [
            'actions' => [
                'create_backup' => 'Crear Copia de Seguridad',
            ],

            'heading' => 'Copias de Seguridad',

            'messages' => [
                'backup_success' => 'Creando una nueva copia de seguridad en segundo plano.',
            ],

            'modal' => [
                'buttons' => [
                    'only_db' => 'Solo Base de Datos',
                    'only_files' => 'Solo Archivos',
                    'db_and_files' => 'Base de Datos y Archivos',
                ],

                'label' => 'Seleccione una opción',
            ],

            'navigation' => [
                'group' => 'Administración',
                'label' => 'Copias de Seguridad',
            ],
        ],
    ],

];
