<?php

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Joaopaulolndev\FilamentGeneralSettings\Models\GeneralSetting;
use Joaopaulolndev\FilamentGeneralSettings\Pages\GeneralSettingsPage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    // Crear roles necesarios
    Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
    Role::create(['name' => 'user', 'guard_name' => 'web']);

    // Crear usuario super_admin
    $this->superAdmin = User::factory()->create([
        'email' => 'admin@test.com',
        'name' => 'Super Admin',
    ]);
    $this->superAdmin->assignRole('super_admin');

    // Crear usuario regular
    $this->regularUser = User::factory()->create([
        'email' => 'user@test.com',
        'name' => 'Regular User',
    ]);
    $this->regularUser->assignRole('user');
});

describe('General Settings Page Access Control', function () {
    it('permite acceso a super_admin', function () {
        actingAs($this->superAdmin);

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        expect(GeneralSettingsPage::canAccess())->toBeTrue();
    });

    it('niega acceso a usuarios regulares', function () {
        actingAs($this->regularUser);

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        expect(GeneralSettingsPage::canAccess())->toBeFalse();
    });

    it('niega acceso a usuarios no autenticados', function () {
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        expect(GeneralSettingsPage::canAccess())->toBeFalse();
    });

    it('muestra la página de configuración general para super_admin', function () {
        actingAs($this->superAdmin);

        Livewire::test(GeneralSettingsPage::class)
            ->assertSuccessful();
    });

    it('verifica que la página tiene el icono correcto', function () {
        actingAs($this->superAdmin);

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        expect(GeneralSettingsPage::getNavigationIcon())
            ->toBe('heroicon-o-cog-6-tooth');
    });

    it('verifica que la página está en el grupo de navegación correcto', function () {
        actingAs($this->superAdmin);

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        expect(GeneralSettingsPage::getNavigationGroup())
            ->toBe('Administración');
    });

    it('verifica que la página tiene la etiqueta de navegación correcta', function () {
        actingAs($this->superAdmin);

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        expect(GeneralSettingsPage::getNavigationLabel())
            ->toBe('Configuración de correo');
    });
});

describe('Email Settings - SMTP Configuration', function () {
    it('puede guardar configuración SMTP completa', function () {
        actingAs($this->superAdmin);

        $emailSettings = [
            'default_email_provider' => 'smtp',
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => '587',
            'smtp_encryption' => 'tls',
            'smtp_timeout' => '30',
            'smtp_username' => 'test@example.com',
            'smtp_password' => 'secret_password',
            'email_from_name' => 'Test Application',
            'email_from_address' => 'noreply@example.com',
        ];

        Livewire::test(GeneralSettingsPage::class)
            ->fillForm($emailSettings)
            ->call('update');

        // Verificar que se guardó en la base de datos
        assertDatabaseHas('general_settings', [
            'email_from_name' => 'Test Application',
            'email_from_address' => 'noreply@example.com',
        ]);

        // Verificar que los settings están en formato JSON
        $settings = GeneralSetting::first();
        expect($settings->email_settings)->toBeArray()
            ->and($settings->email_settings['default_email_provider'])->toBe('smtp')
            ->and($settings->email_settings['smtp_host'])->toBe('smtp.example.com')
            ->and($settings->email_settings['smtp_port'])->toBe('587')
            ->and($settings->email_settings['smtp_encryption'])->toBe('tls')
            ->and($settings->email_settings['smtp_username'])->toBe('test@example.com')
            ->and($settings->email_settings['smtp_password'])->toBe('secret_password');
    });

    it('puede actualizar configuración SMTP existente', function () {
        actingAs($this->superAdmin);

        // Crear configuración inicial
        GeneralSetting::create([
            'email_settings' => [
                'default_email_provider' => 'smtp',
                'smtp_host' => 'old.example.com',
                'smtp_port' => '465',
            ],
            'email_from_name' => 'Old Name',
            'email_from_address' => 'old@example.com',
        ]);

        // Actualizar configuración
        $newSettings = [
            'default_email_provider' => 'smtp',
            'smtp_host' => 'new.example.com',
            'smtp_port' => '587',
            'smtp_encryption' => 'tls',
            'smtp_timeout' => '60',
            'smtp_username' => 'new@example.com',
            'smtp_password' => 'new_password',
            'email_from_name' => 'New Name',
            'email_from_address' => 'new@example.com',
        ];

        Livewire::test(GeneralSettingsPage::class)
            ->fillForm($newSettings)
            ->call('update');

        $settings = GeneralSetting::first();
        expect($settings->email_settings['smtp_host'])->toBe('new.example.com')
            ->and($settings->email_settings['smtp_port'])->toBe('587')
            ->and($settings->email_from_name)->toBe('New Name')
            ->and($settings->email_from_address)->toBe('new@example.com');
    });

    it('valida que solo exista un registro de configuración general', function () {
        actingAs($this->superAdmin);

        // Crear primer registro
        Livewire::test(GeneralSettingsPage::class)
            ->fillForm([
                'default_email_provider' => 'smtp',
                'smtp_host' => 'smtp1.example.com',
                'email_from_name' => 'First',
                'email_from_address' => 'first@example.com',
            ])
            ->call('update');

        expect(GeneralSetting::count())->toBe(1);

        // Actualizar (debería hacer update, no create)
        Livewire::test(GeneralSettingsPage::class)
            ->fillForm([
                'default_email_provider' => 'smtp',
                'smtp_host' => 'smtp2.example.com',
                'email_from_name' => 'Second',
                'email_from_address' => 'second@example.com',
            ])
            ->call('update');

        expect(GeneralSetting::count())->toBe(1);

        $settings = GeneralSetting::first();
        expect($settings->email_settings['smtp_host'])->toBe('smtp2.example.com');
    });
});

describe('Email Settings - Other Providers', function () {
    it('puede guardar configuración de Mailgun', function () {
        actingAs($this->superAdmin);

        $emailSettings = [
            'default_email_provider' => 'mailgun',
            'mailgun_domain' => 'mg.example.com',
            'mailgun_secret' => 'key-secret123',
            'mailgun_endpoint' => 'api.mailgun.net',
            'email_from_name' => 'Mailgun Test',
            'email_from_address' => 'test@mg.example.com',
        ];

        Livewire::test(GeneralSettingsPage::class)
            ->fillForm($emailSettings)
            ->call('update');

        $settings = GeneralSetting::first();
        expect($settings->email_settings['default_email_provider'])->toBe('mailgun')
            ->and($settings->email_settings['mailgun_domain'])->toBe('mg.example.com')
            ->and($settings->email_settings['mailgun_secret'])->toBe('key-secret123')
            ->and($settings->email_settings['mailgun_endpoint'])->toBe('api.mailgun.net');
    });

    it('puede guardar configuración de Postmark', function () {
        actingAs($this->superAdmin);

        $emailSettings = [
            'default_email_provider' => 'postmark',
            'postmark_token' => 'pm-token-123456',
            'email_from_name' => 'Postmark Test',
            'email_from_address' => 'test@postmark.example.com',
        ];

        Livewire::test(GeneralSettingsPage::class)
            ->fillForm($emailSettings)
            ->call('update');

        $settings = GeneralSetting::first();
        expect($settings->email_settings['default_email_provider'])->toBe('postmark')
            ->and($settings->email_settings['postmark_token'])->toBe('pm-token-123456');
    });

    it('puede guardar configuración de Amazon SES', function () {
        actingAs($this->superAdmin);

        $emailSettings = [
            'default_email_provider' => 'ses',
            'amazon_ses_key' => 'AKIAIOSFODNN7EXAMPLE',
            'amazon_ses_secret' => 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY',
            'amazon_ses_region' => 'us-west-2',
            'email_from_name' => 'SES Test',
            'email_from_address' => 'test@ses.example.com',
        ];

        Livewire::test(GeneralSettingsPage::class)
            ->fillForm($emailSettings)
            ->call('update');

        $settings = GeneralSetting::first();
        expect($settings->email_settings['default_email_provider'])->toBe('ses')
            ->and($settings->email_settings['amazon_ses_key'])->toBe('AKIAIOSFODNN7EXAMPLE')
            ->and($settings->email_settings['amazon_ses_secret'])->toBe('wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY')
            ->and($settings->email_settings['amazon_ses_region'])->toBe('us-west-2');
    });
});

describe('Email Settings - Cache Management', function () {
    it('limpia el cache cuando se actualizan los settings', function () {
        actingAs($this->superAdmin);

        // Establecer un valor en cache
        Cache::put('general_settings', ['test' => 'value'], 3600);
        expect(Cache::has('general_settings'))->toBeTrue();

        // Actualizar configuración
        Livewire::test(GeneralSettingsPage::class)
            ->fillForm([
                'default_email_provider' => 'smtp',
                'smtp_host' => 'smtp.example.com',
                'email_from_name' => 'Test',
                'email_from_address' => 'test@example.com',
            ])
            ->call('update');

        // Verificar que el cache se limpió
        expect(Cache::has('general_settings'))->toBeFalse();
    });
});

describe('Email Settings - Test Email Functionality', function () {
    it('puede enviar email de prueba con configuración SMTP', function () {
        actingAs($this->superAdmin);

        Mail::fake();

        GeneralSetting::create([
            'email_settings' => [
                'default_email_provider' => 'smtp',
                'smtp_host' => 'smtp.example.com',
                'smtp_port' => '587',
                'smtp_encryption' => 'tls',
                'smtp_username' => 'test@example.com',
                'smtp_password' => 'password',
            ],
            'email_from_name' => 'Test App',
            'email_from_address' => 'noreply@example.com',
        ]);

        Livewire::test(GeneralSettingsPage::class)
            ->fillForm([
                'default_email_provider' => 'smtp',
                'smtp_host' => 'smtp.example.com',
                'smtp_port' => '587',
                'smtp_encryption' => 'tls',
                'smtp_username' => 'test@example.com',
                'smtp_password' => 'password',
                'email_from_name' => 'Test App',
                'email_from_address' => 'noreply@example.com',
                'mail_to' => 'recipient@example.com',
            ])
            ->call('sendTestMail');

        Mail::assertSent(function (\Joaopaulolndev\FilamentGeneralSettings\Mail\TestMail $mail) {
            return $mail->hasTo('recipient@example.com');
        });
    });

    it('muestra notificación de error cuando falla el envío de email', function () {
        actingAs($this->superAdmin);

        // Configurar Mail para que falle
        Mail::shouldReceive('mailer')
            ->andThrow(new \Exception('SMTP connection failed'));

        GeneralSetting::create([
            'email_settings' => [
                'default_email_provider' => 'smtp',
                'smtp_host' => 'invalid.smtp.com',
            ],
            'email_from_name' => 'Test',
            'email_from_address' => 'test@example.com',
        ]);

        Livewire::test(GeneralSettingsPage::class)
            ->fillForm([
                'default_email_provider' => 'smtp',
                'smtp_host' => 'invalid.smtp.com',
                'mail_to' => 'test@example.com',
            ])
            ->call('sendTestMail')
            ->assertNotified();
    });

    it('verifica que el campo mail_to existe en el formulario', function () {
        actingAs($this->superAdmin);

        Livewire::test(GeneralSettingsPage::class)
            ->assertFormFieldExists('mail_to');
    });
});

describe('Email Settings - Data Persistence', function () {
    it('carga correctamente los settings existentes al montar el componente', function () {
        actingAs($this->superAdmin);

        // Crear configuración previa
        GeneralSetting::create([
            'email_settings' => [
                'default_email_provider' => 'smtp',
                'smtp_host' => 'existing.smtp.com',
                'smtp_port' => '465',
                'smtp_encryption' => 'ssl',
            ],
            'email_from_name' => 'Existing App',
            'email_from_address' => 'existing@example.com',
        ]);

        Livewire::test(GeneralSettingsPage::class)
            ->assertFormSet([
                'default_email_provider' => 'smtp',
                'smtp_host' => 'existing.smtp.com',
                'smtp_port' => '465',
                'smtp_encryption' => 'ssl',
                'email_from_name' => 'Existing App',
                'email_from_address' => 'existing@example.com',
            ]);
    });

    it('inicializa con valores por defecto cuando no hay configuración', function () {
        actingAs($this->superAdmin);

        Livewire::test(GeneralSettingsPage::class)
            ->assertFormSet([
                'default_email_provider' => 'smtp',
            ]);
    });
});

describe('Email Settings - Form Validation', function () {
    it('valida que email_from_address sea un email válido', function () {
        actingAs($this->superAdmin);

        Livewire::test(GeneralSettingsPage::class)
            ->fillForm([
                'email_from_address' => 'invalid-email',
            ])
            ->call('update')
            ->assertHasFormErrors(['email_from_address']);
    });

    it('acepta email_from_address válido', function () {
        actingAs($this->superAdmin);

        Livewire::test(GeneralSettingsPage::class)
            ->fillForm([
                'default_email_provider' => 'smtp',
                'email_from_address' => 'valid@example.com',
                'email_from_name' => 'Valid Name',
            ])
            ->call('update')
            ->assertHasNoFormErrors();
    });
});

describe('Email Settings - Security', function () {
    it('almacena las credenciales en el campo email_settings como JSON', function () {
        actingAs($this->superAdmin);

        Livewire::test(GeneralSettingsPage::class)
            ->fillForm([
                'default_email_provider' => 'smtp',
                'smtp_username' => 'secure_user',
                'smtp_password' => 'secure_password_123',
            ])
            ->call('update');

        $settings = GeneralSetting::first();

        // Verificar que se almacena en JSON
        expect($settings->email_settings)->toBeArray()
            ->and($settings->email_settings['smtp_username'])->toBe('secure_user')
            ->and($settings->email_settings['smtp_password'])->toBe('secure_password_123');
    });

    it('no expone credenciales sensibles en campos individuales de la tabla', function () {
        actingAs($this->superAdmin);

        Livewire::test(GeneralSettingsPage::class)
            ->fillForm([
                'default_email_provider' => 'smtp',
                'smtp_password' => 'super_secret_password',
            ])
            ->call('update');

        // Verificar que no hay columna smtp_password en la tabla principal
        $settings = GeneralSetting::first();
        expect($settings->getAttributes())->not->toHaveKey('smtp_password')
            ->and($settings->email_settings['smtp_password'])->toBe('super_secret_password');
    });
});

describe('Email Settings - Tab Configuration', function () {
    it('muestra solo el tab de Email según configuración', function () {
        actingAs($this->superAdmin);

        expect(config('filament-general-settings.show_email_tab'))->toBeTrue()
            ->and(config('filament-general-settings.show_application_tab'))->toBeFalse()
            ->and(config('filament-general-settings.show_analytics_tab'))->toBeFalse()
            ->and(config('filament-general-settings.show_seo_tab'))->toBeFalse()
            ->and(config('filament-general-settings.show_social_networks_tab'))->toBeFalse();
    });

    it('tiene configurado el tiempo de expiración del cache', function () {
        expect(config('filament-general-settings.expiration_cache_config_time'))
            ->toBe(60);
    });
});
