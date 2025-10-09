<?php

use Illuminate\Support\Facades\Schema;
use Joaopaulolndev\FilamentGeneralSettings\Helpers\EmailDataHelper;
use Joaopaulolndev\FilamentGeneralSettings\Models\GeneralSetting;

describe('GeneralSetting Model - Database Structure', function () {
    it('verifica que la tabla general_settings existe', function () {
        expect(Schema::hasTable('general_settings'))->toBeTrue();
    });

    it('verifica que la tabla tiene todas las columnas requeridas', function () {
        $columns = [
            'id',
            'site_name',
            'site_description',
            'site_logo',
            'site_favicon',
            'theme_color',
            'support_email',
            'support_phone',
            'google_analytics_id',
            'posthog_html_snippet',
            'seo_title',
            'seo_keywords',
            'seo_metadata',
            'email_settings',
            'email_from_address',
            'email_from_name',
            'social_network',
            'more_configs',
            'created_at',
            'updated_at',
        ];

        foreach ($columns as $column) {
            expect(Schema::hasColumn('general_settings', $column))
                ->toBeTrue("Column {$column} should exist in general_settings table");
        }
    });

    it('verifica que las columnas JSON tienen el tipo correcto', function () {
        $jsonColumns = [
            'seo_metadata',
            'email_settings',
            'social_network',
            'more_configs',
        ];

        foreach ($jsonColumns as $column) {
            $columnType = Schema::getColumnType('general_settings', $column);
            expect(in_array($columnType, ['json', 'text']))
                ->toBeTrue("Column {$column} should be JSON or TEXT type, got {$columnType}");
        }
    });
});

describe('GeneralSetting Model - Fillable Attributes', function () {
    it('permite asignar todos los campos fillable', function () {
        $fillableFields = [
            'site_name',
            'site_description',
            'site_logo',
            'site_favicon',
            'theme_color',
            'support_email',
            'support_phone',
            'google_analytics_id',
            'posthog_html_snippet',
            'seo_title',
            'seo_keywords',
            'seo_metadata',
            'social_network',
            'email_settings',
            'email_from_name',
            'email_from_address',
            'more_configs',
        ];

        $model = new GeneralSetting;

        foreach ($fillableFields as $field) {
            expect($model->isFillable($field))
                ->toBeTrue("Field {$field} should be fillable");
        }
    });

    it('puede crear un registro con mass assignment', function () {
        $data = [
            'site_name' => 'Test Site',
            'email_from_name' => 'Test Mailer',
            'email_from_address' => 'test@example.com',
            'email_settings' => [
                'default_email_provider' => 'smtp',
                'smtp_host' => 'smtp.test.com',
            ],
        ];

        $setting = GeneralSetting::create($data);

        expect($setting)->toBeInstanceOf(GeneralSetting::class)
            ->and($setting->site_name)->toBe('Test Site')
            ->and($setting->email_from_name)->toBe('Test Mailer')
            ->and($setting->email_from_address)->toBe('test@example.com');
    });
});

describe('GeneralSetting Model - Casts', function () {
    it('convierte email_settings a array automáticamente', function () {
        $setting = GeneralSetting::create([
            'email_settings' => [
                'smtp_host' => 'smtp.example.com',
                'smtp_port' => '587',
            ],
        ]);

        expect($setting->email_settings)->toBeArray()
            ->and($setting->email_settings['smtp_host'])->toBe('smtp.example.com')
            ->and($setting->email_settings['smtp_port'])->toBe('587');
    });

    it('convierte seo_metadata a array automáticamente', function () {
        $setting = GeneralSetting::create([
            'seo_metadata' => [
                'title' => 'SEO Title',
                'description' => 'SEO Description',
            ],
        ]);

        expect($setting->seo_metadata)->toBeArray()
            ->and($setting->seo_metadata['title'])->toBe('SEO Title');
    });

    it('convierte social_network a array automáticamente', function () {
        $setting = GeneralSetting::create([
            'social_network' => [
                'facebook' => 'https://facebook.com/test',
                'twitter' => 'https://twitter.com/test',
            ],
        ]);

        expect($setting->social_network)->toBeArray()
            ->and($setting->social_network['facebook'])->toBe('https://facebook.com/test');
    });

    it('convierte more_configs a array automáticamente', function () {
        $setting = GeneralSetting::create([
            'more_configs' => [
                'custom_field' => 'custom_value',
            ],
        ]);

        expect($setting->more_configs)->toBeArray()
            ->and($setting->more_configs['custom_field'])->toBe('custom_value');
    });

    it('maneja valores null en campos JSON', function () {
        $setting = GeneralSetting::create([
            'email_settings' => null,
            'seo_metadata' => null,
        ]);

        expect($setting->email_settings)->toBeNull()
            ->and($setting->seo_metadata)->toBeNull();
    });
});

describe('GeneralSetting Model - Email Settings Structure', function () {
    it('puede almacenar configuración SMTP completa', function () {
        $emailSettings = [
            'default_email_provider' => 'smtp',
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => '587',
            'smtp_encryption' => 'tls',
            'smtp_timeout' => '30',
            'smtp_username' => 'user@gmail.com',
            'smtp_password' => 'app_password_123',
        ];

        $setting = GeneralSetting::create([
            'email_settings' => $emailSettings,
        ]);

        expect($setting->email_settings)->toBe($emailSettings);
    });

    it('puede almacenar configuración de múltiples proveedores', function () {
        $emailSettings = [
            'default_email_provider' => 'mailgun',
            'mailgun_domain' => 'mg.example.com',
            'mailgun_secret' => 'key-123456',
            'mailgun_endpoint' => 'api.mailgun.net',
            'smtp_host' => 'smtp.example.com', // Puede tener múltiples configs
            'smtp_port' => '587',
        ];

        $setting = GeneralSetting::create([
            'email_settings' => $emailSettings,
        ]);

        expect($setting->email_settings['default_email_provider'])->toBe('mailgun')
            ->and($setting->email_settings['smtp_host'])->toBe('smtp.example.com');
    });

    it('actualiza solo campos específicos de email_settings', function () {
        $setting = GeneralSetting::create([
            'email_settings' => [
                'smtp_host' => 'old.smtp.com',
                'smtp_port' => '465',
                'smtp_username' => 'old@example.com',
            ],
        ]);

        // Actualizar solo algunos campos
        $newSettings = $setting->email_settings;
        $newSettings['smtp_host'] = 'new.smtp.com';
        $setting->update(['email_settings' => $newSettings]);

        $setting->refresh();

        expect($setting->email_settings['smtp_host'])->toBe('new.smtp.com')
            ->and($setting->email_settings['smtp_port'])->toBe('465')
            ->and($setting->email_settings['smtp_username'])->toBe('old@example.com');
    });
});

describe('GeneralSetting Model - CRUD Operations', function () {
    it('puede crear un nuevo registro', function () {
        $setting = GeneralSetting::create([
            'site_name' => 'Test Application',
            'email_from_name' => 'Test',
            'email_from_address' => 'test@example.com',
        ]);

        expect($setting->exists)->toBeTrue()
            ->and($setting->site_name)->toBe('Test Application')
            ->and(GeneralSetting::count())->toBe(1);
    });

    it('puede actualizar un registro existente', function () {
        $setting = GeneralSetting::create([
            'site_name' => 'Original Name',
        ]);

        $setting->update([
            'site_name' => 'Updated Name',
        ]);

        expect($setting->site_name)->toBe('Updated Name')
            ->and(GeneralSetting::count())->toBe(1);
    });

    it('puede eliminar un registro', function () {
        $setting = GeneralSetting::create([
            'site_name' => 'To Delete',
        ]);

        expect(GeneralSetting::count())->toBe(1);

        $setting->delete();

        expect(GeneralSetting::count())->toBe(0);
    });

    it('updateOrCreate funciona correctamente para singleton pattern', function () {
        // Primera vez - debe crear
        $setting = GeneralSetting::updateOrCreate(
            [],
            ['site_name' => 'First']
        );

        expect(GeneralSetting::count())->toBe(1)
            ->and($setting->site_name)->toBe('First');

        // Segunda vez - debe actualizar
        $setting = GeneralSetting::updateOrCreate(
            [],
            ['site_name' => 'Updated']
        );

        expect(GeneralSetting::count())->toBe(1)
            ->and($setting->site_name)->toBe('Updated');
    });
});

describe('EmailDataHelper - Data Transformation', function () {
    it('extrae configuración de email desde database format', function () {
        $databaseData = [
            'email_settings' => [
                'default_email_provider' => 'smtp',
                'smtp_host' => 'smtp.example.com',
                'smtp_port' => '587',
                'smtp_encryption' => 'tls',
                'smtp_username' => 'user@example.com',
                'smtp_password' => 'password123',
            ],
        ];

        $result = EmailDataHelper::getEmailConfigFromDatabase($databaseData);

        expect($result['default_email_provider'])->toBe('smtp')
            ->and($result['smtp_host'])->toBe('smtp.example.com')
            ->and($result['smtp_port'])->toBe('587')
            ->and($result['smtp_encryption'])->toBe('tls')
            ->and($result['smtp_username'])->toBe('user@example.com')
            ->and($result['smtp_password'])->toBe('password123');
    });

    it('usa valores por defecto cuando email_settings está vacío', function () {
        $databaseData = [
            'email_settings' => [],
        ];

        $result = EmailDataHelper::getEmailConfigFromDatabase($databaseData);

        expect($result['default_email_provider'])->toBe('smtp')
            ->and($result['smtp_host'])->toBeNull()
            ->and($result['smtp_port'])->toBeNull();
    });

    it('convierte datos de formulario a formato database', function () {
        $formData = [
            'default_email_provider' => 'smtp',
            'smtp_host' => 'smtp.test.com',
            'smtp_port' => '465',
            'smtp_encryption' => 'ssl',
            'smtp_username' => 'test@test.com',
            'smtp_password' => 'secret',
            'other_field' => 'should_remain',
        ];

        $result = EmailDataHelper::setEmailConfigToDatabase($formData);

        expect($result['email_settings'])->toBeArray()
            ->and($result['email_settings']['default_email_provider'])->toBe('smtp')
            ->and($result['email_settings']['smtp_host'])->toBe('smtp.test.com')
            ->and($result['email_settings']['smtp_port'])->toBe('465')
            ->and($result['other_field'])->toBe('should_remain');
    });

    it('maneja configuración de Mailgun correctamente', function () {
        $formData = [
            'default_email_provider' => 'mailgun',
            'mailgun_domain' => 'mg.example.com',
            'mailgun_secret' => 'key-secret',
            'mailgun_endpoint' => 'api.mailgun.net',
        ];

        $result = EmailDataHelper::setEmailConfigToDatabase($formData);

        expect($result['email_settings']['default_email_provider'])->toBe('mailgun')
            ->and($result['email_settings']['mailgun_domain'])->toBe('mg.example.com')
            ->and($result['email_settings']['mailgun_secret'])->toBe('key-secret')
            ->and($result['email_settings']['mailgun_endpoint'])->toBe('api.mailgun.net');
    });

    it('maneja configuración de Postmark correctamente', function () {
        $formData = [
            'default_email_provider' => 'postmark',
            'postmark_token' => 'pm-token-123',
        ];

        $result = EmailDataHelper::setEmailConfigToDatabase($formData);

        expect($result['email_settings']['default_email_provider'])->toBe('postmark')
            ->and($result['email_settings']['postmark_token'])->toBe('pm-token-123');
    });

    it('maneja configuración de Amazon SES correctamente', function () {
        $formData = [
            'default_email_provider' => 'ses',
            'amazon_ses_key' => 'AKIAIOSFODNN7EXAMPLE',
            'amazon_ses_secret' => 'secret_key',
            'amazon_ses_region' => 'us-east-1',
        ];

        $result = EmailDataHelper::setEmailConfigToDatabase($formData);

        expect($result['email_settings']['default_email_provider'])->toBe('ses')
            ->and($result['email_settings']['amazon_ses_key'])->toBe('AKIAIOSFODNN7EXAMPLE')
            ->and($result['email_settings']['amazon_ses_secret'])->toBe('secret_key')
            ->and($result['email_settings']['amazon_ses_region'])->toBe('us-east-1');
    });

    it('preserva datos existentes al transformar', function () {
        $databaseData = [
            'site_name' => 'My Site',
            'support_email' => 'support@example.com',
            'email_settings' => [
                'smtp_host' => 'smtp.example.com',
            ],
        ];

        $result = EmailDataHelper::getEmailConfigFromDatabase($databaseData);

        expect($result['site_name'])->toBe('My Site')
            ->and($result['support_email'])->toBe('support@example.com')
            ->and($result['smtp_host'])->toBe('smtp.example.com');
    });
});

describe('GeneralSetting Model - Timestamps', function () {
    it('registra created_at y updated_at automáticamente', function () {
        $setting = GeneralSetting::create([
            'site_name' => 'Test',
        ]);

        expect($setting->created_at)->not->toBeNull()
            ->and($setting->updated_at)->not->toBeNull()
            ->and($setting->created_at->toDateTimeString())->toBe($setting->updated_at->toDateTimeString());
    });

    it('actualiza updated_at al modificar el registro', function () {
        $setting = GeneralSetting::create([
            'site_name' => 'Original',
        ]);

        $originalUpdatedAt = $setting->updated_at;

        // Esperar un segundo para asegurar que el timestamp cambie
        sleep(1);

        $setting->update([
            'site_name' => 'Modified',
        ]);

        expect($setting->updated_at->isAfter($originalUpdatedAt))->toBeTrue();
    });
});
