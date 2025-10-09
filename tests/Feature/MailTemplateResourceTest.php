<?php

declare(strict_types=1);

use App\Filament\Resources\MailTemplateResource;
use App\Models\MailTemplate;
use App\Models\User;
use Spatie\Permission\Models\Permission;

use function Pest\Livewire\livewire;

beforeEach(function () {
    // Create and authenticate a user with all mail template permissions
    $user = User::factory()->create();

    // Create and assign all necessary permissions
    $permissions = [
        'view_any_mail::template',
        'view_mail::template',
        'create_mail::template',
        'update_mail::template',
        'delete_mail::template',
        'delete_any_mail::template',
        'force_delete_mail::template',
        'force_delete_any_mail::template',
        'restore_mail::template',
        'restore_any_mail::template',
        'replicate_mail::template',
        'reorder_mail::template',
    ];

    foreach ($permissions as $permissionName) {
        $permission = Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        $user->givePermissionTo($permission);
    }

    $this->actingAs($user);
});

test('can render mail template resource list page', function () {
    livewire(MailTemplateResource\Pages\ListMailTemplates::class)
        ->assertSuccessful();
});

test('can render mail template resource create page', function () {
    livewire(MailTemplateResource\Pages\CreateMailTemplate::class)
        ->assertSuccessful();
});

test('can render mail template resource edit page', function () {
    $mailTemplate = MailTemplate::factory()->create();

    livewire(MailTemplateResource\Pages\EditMailTemplate::class, ['record' => $mailTemplate->id])
        ->assertSuccessful();
});

test('can list mail templates', function () {
    $mailTemplates = MailTemplate::factory()->count(5)->create();

    livewire(MailTemplateResource\Pages\ListMailTemplates::class)
        ->assertCanSeeTableRecords($mailTemplates);
});

test('can create mail template with all required fields', function () {
    livewire(MailTemplateResource\Pages\CreateMailTemplate::class)
        ->fillForm([
            'name' => 'Test Template',
            'category' => 'incidents',
            'mailable' => 'App\\Mail\\IncidentCreatedMail',
            'subject' => 'Test Subject: {{ incident.title }}',
            'html_template' => '<p>Test email content</p>',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('mail_templates', [
        'name' => 'Test Template',
        'category' => 'incidents',
        'mailable' => 'App\\Mail\\IncidentCreatedMail',
        'subject' => 'Test Subject: {{ incident.title }}',
    ]);
});

test('can create mail template with optional text_template', function () {
    livewire(MailTemplateResource\Pages\CreateMailTemplate::class)
        ->fillForm([
            'name' => 'Template with Text',
            'category' => 'documents',
            'mailable' => 'App\\Mail\\DocumentUploadedMail',
            'subject' => 'Document Uploaded',
            'html_template' => '<p>HTML version</p>',
            'text_template' => 'Plain text version',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('mail_templates', [
        'name' => 'Template with Text',
        'text_template' => 'Plain text version',
    ]);
});

test('name is required', function () {
    livewire(MailTemplateResource\Pages\CreateMailTemplate::class)
        ->fillForm([
            'category' => 'incidents',
            'mailable' => 'App\\Mail\\IncidentCreatedMail',
            'subject' => 'Test Subject',
            'html_template' => '<p>Content</p>',
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);
});

test('category is required', function () {
    livewire(MailTemplateResource\Pages\CreateMailTemplate::class)
        ->fillForm([
            'name' => 'Test Template',
            'mailable' => 'App\\Mail\\IncidentCreatedMail',
            'subject' => 'Test Subject',
            'html_template' => '<p>Content</p>',
        ])
        ->call('create')
        ->assertHasFormErrors(['category' => 'required']);
});

test('mailable is required', function () {
    livewire(MailTemplateResource\Pages\CreateMailTemplate::class)
        ->fillForm([
            'name' => 'Test Template',
            'category' => 'incidents',
            'subject' => 'Test Subject',
            'html_template' => '<p>Content</p>',
        ])
        ->call('create')
        ->assertHasFormErrors(['mailable' => 'required']);
});

test('subject is required', function () {
    livewire(MailTemplateResource\Pages\CreateMailTemplate::class)
        ->fillForm([
            'name' => 'Test Template',
            'category' => 'incidents',
            'mailable' => 'App\\Mail\\IncidentCreatedMail',
            'html_template' => '<p>Content</p>',
        ])
        ->call('create')
        ->assertHasFormErrors(['subject' => 'required']);
});

test('html_template is required', function () {
    livewire(MailTemplateResource\Pages\CreateMailTemplate::class)
        ->fillForm([
            'name' => 'Test Template',
            'category' => 'incidents',
            'mailable' => 'App\\Mail\\IncidentCreatedMail',
            'subject' => 'Test Subject',
        ])
        ->call('create')
        ->assertHasFormErrors(['html_template' => 'required']);
});

test('can edit mail template', function () {
    $mailTemplate = MailTemplate::factory()->create();

    livewire(MailTemplateResource\Pages\EditMailTemplate::class, ['record' => $mailTemplate->id])
        ->fillForm([
            'subject' => 'Updated Subject',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('mail_templates', [
        'id' => $mailTemplate->id,
        'subject' => 'Updated Subject',
    ]);
});

test('can delete mail template', function () {
    $mailTemplate = MailTemplate::factory()->create();

    livewire(MailTemplateResource\Pages\EditMailTemplate::class, ['record' => $mailTemplate->id])
        ->callAction('delete');

    $this->assertSoftDeleted('mail_templates', [
        'id' => $mailTemplate->id,
    ]);
});

test('can restore deleted mail template', function () {
    $mailTemplate = MailTemplate::factory()->create();
    $mailTemplate->delete();

    livewire(MailTemplateResource\Pages\EditMailTemplate::class, ['record' => $mailTemplate->id])
        ->callAction('restore');

    $this->assertDatabaseHas('mail_templates', [
        'id' => $mailTemplate->id,
        'deleted_at' => null,
    ]);
});

test('can force delete mail template', function () {
    $mailTemplate = MailTemplate::factory()->create();
    $mailTemplate->delete();

    livewire(MailTemplateResource\Pages\EditMailTemplate::class, ['record' => $mailTemplate->id])
        ->callAction('forceDelete');

    $this->assertDatabaseMissing('mail_templates', [
        'id' => $mailTemplate->id,
    ]);
});

test('can filter mail templates by category', function () {
    $incidentTemplate = MailTemplate::factory()->incidentCreated()->create();
    $documentTemplate = MailTemplate::factory()->documentExpiring()->create();

    livewire(MailTemplateResource\Pages\ListMailTemplates::class)
        ->filterTable('category', 'incidents')
        ->assertCanSeeTableRecords([$incidentTemplate])
        ->assertCanNotSeeTableRecords([$documentTemplate]);
});

test('can filter trashed mail templates only', function () {
    $activeTemplate = MailTemplate::factory()->create();
    $trashedTemplate = MailTemplate::factory()->create();
    $trashedTemplate->delete();

    livewire(MailTemplateResource\Pages\ListMailTemplates::class)
        ->filterTable('trashed', 'only')
        ->assertCanSeeTableRecords([$trashedTemplate]);
});

test('can filter to show all mail templates including trashed', function () {
    $activeTemplate = MailTemplate::factory()->create();
    $trashedTemplate = MailTemplate::factory()->create();
    $trashedTemplate->delete();

    livewire(MailTemplateResource\Pages\ListMailTemplates::class)
        ->filterTable('trashed', 'with')
        ->assertCanSeeTableRecords([$activeTemplate, $trashedTemplate]);
});

test('can search mail templates by name', function () {
    $template1 = MailTemplate::factory()->create([
        'name' => 'Incident Created Template XYZ123',
        'subject' => 'Subject 1',
    ]);
    $template2 = MailTemplate::factory()->create([
        'name' => 'Document Uploaded Template ABC456',
        'subject' => 'Subject 2',
    ]);

    livewire(MailTemplateResource\Pages\ListMailTemplates::class)
        ->searchTable('XYZ123')
        ->assertCanSeeTableRecords([$template1])
        ->assertCanNotSeeTableRecords([$template2]);
});

test('can search mail templates by subject', function () {
    $template1 = MailTemplate::factory()->create(['subject' => 'New Incident Alert']);
    $template2 = MailTemplate::factory()->create(['subject' => 'Document Expiring Soon']);

    livewire(MailTemplateResource\Pages\ListMailTemplates::class)
        ->searchTable('Alert')
        ->assertCanSeeTableRecords([$template1])
        ->assertCanNotSeeTableRecords([$template2]);
});

test('can use table edit action', function () {
    $mailTemplate = MailTemplate::factory()->create();

    livewire(MailTemplateResource\Pages\ListMailTemplates::class)
        ->callTableAction('edit', $mailTemplate);
});

test('can use table delete action', function () {
    $mailTemplate = MailTemplate::factory()->create();

    livewire(MailTemplateResource\Pages\ListMailTemplates::class)
        ->callTableAction('delete', $mailTemplate);

    $this->assertSoftDeleted('mail_templates', ['id' => $mailTemplate->id]);
});

test('can use table preview action', function () {
    $mailTemplate = MailTemplate::factory()->create();

    livewire(MailTemplateResource\Pages\ListMailTemplates::class)
        ->callTableAction('preview', $mailTemplate)
        ->assertSuccessful();
});

test('can bulk delete mail templates', function () {
    $mailTemplates = MailTemplate::factory()->count(3)->create();

    livewire(MailTemplateResource\Pages\ListMailTemplates::class)
        ->callTableBulkAction('delete', $mailTemplates);

    foreach ($mailTemplates as $mailTemplate) {
        $this->assertSoftDeleted('mail_templates', ['id' => $mailTemplate->id]);
    }
});

test('can bulk restore mail templates', function () {
    $mailTemplates = MailTemplate::factory()->count(3)->create();
    foreach ($mailTemplates as $mailTemplate) {
        $mailTemplate->delete();
    }

    livewire(MailTemplateResource\Pages\ListMailTemplates::class)
        ->filterTable('trashed', 'only')
        ->callTableBulkAction('restore', $mailTemplates);

    foreach ($mailTemplates as $mailTemplate) {
        $this->assertDatabaseHas('mail_templates', [
            'id' => $mailTemplate->id,
            'deleted_at' => null,
        ]);
    }
});

test('can bulk force delete mail templates', function () {
    $mailTemplates = MailTemplate::factory()->count(3)->create();
    foreach ($mailTemplates as $mailTemplate) {
        $mailTemplate->delete();
    }

    livewire(MailTemplateResource\Pages\ListMailTemplates::class)
        ->filterTable('trashed', 'only')
        ->callTableBulkAction('forceDelete', $mailTemplates);

    foreach ($mailTemplates as $mailTemplate) {
        $this->assertDatabaseMissing('mail_templates', ['id' => $mailTemplate->id]);
    }
});

test('create notification has icon, title and body', function () {
    livewire(MailTemplateResource\Pages\CreateMailTemplate::class)
        ->fillForm([
            'name' => 'Test Template',
            'category' => 'incidents',
            'mailable' => 'App\\Mail\\IncidentCreatedMail',
            'subject' => 'Test Subject',
            'html_template' => '<p>Content</p>',
        ])
        ->call('create')
        ->assertNotified();
});

test('create redirects to index page', function () {
    livewire(MailTemplateResource\Pages\CreateMailTemplate::class)
        ->fillForm([
            'name' => 'Test Template',
            'category' => 'incidents',
            'mailable' => 'App\\Mail\\IncidentCreatedMail',
            'subject' => 'Test Subject',
            'html_template' => '<p>Content</p>',
        ])
        ->call('create')
        ->assertRedirect(MailTemplateResource::getUrl('index'));
});

test('update notification has icon, title and body', function () {
    $mailTemplate = MailTemplate::factory()->create();

    livewire(MailTemplateResource\Pages\EditMailTemplate::class, ['record' => $mailTemplate->id])
        ->fillForm([
            'subject' => 'Updated Subject',
        ])
        ->call('save')
        ->assertNotified();
});

test('update redirects to index page', function () {
    $mailTemplate = MailTemplate::factory()->create();

    livewire(MailTemplateResource\Pages\EditMailTemplate::class, ['record' => $mailTemplate->id])
        ->fillForm([
            'subject' => 'Updated Subject',
        ])
        ->call('save')
        ->assertRedirect(MailTemplateResource::getUrl('index'));
});

test('delete notification has icon, title and body', function () {
    $mailTemplate = MailTemplate::factory()->create();

    livewire(MailTemplateResource\Pages\EditMailTemplate::class, ['record' => $mailTemplate->id])
        ->callAction('delete')
        ->assertNotified();
});

test('restore notification has icon, title and body', function () {
    $mailTemplate = MailTemplate::factory()->create();
    $mailTemplate->delete();

    livewire(MailTemplateResource\Pages\EditMailTemplate::class, ['record' => $mailTemplate->id])
        ->callAction('restore')
        ->assertNotified();
});

test('force delete notification has icon, title and body', function () {
    $mailTemplate = MailTemplate::factory()->create();
    $mailTemplate->delete();

    livewire(MailTemplateResource\Pages\EditMailTemplate::class, ['record' => $mailTemplate->id])
        ->callAction('forceDelete')
        ->assertNotified();
});

test('table delete action shows notification', function () {
    $mailTemplate = MailTemplate::factory()->create();

    livewire(MailTemplateResource\Pages\ListMailTemplates::class)
        ->callTableAction('delete', $mailTemplate)
        ->assertNotified();
});

test('bulk delete shows notification', function () {
    $mailTemplates = MailTemplate::factory()->count(3)->create();

    livewire(MailTemplateResource\Pages\ListMailTemplates::class)
        ->callTableBulkAction('delete', $mailTemplates)
        ->assertNotified();
});

test('bulk restore shows notification', function () {
    $mailTemplates = MailTemplate::factory()->count(3)->create();
    foreach ($mailTemplates as $mailTemplate) {
        $mailTemplate->delete();
    }

    livewire(MailTemplateResource\Pages\ListMailTemplates::class)
        ->filterTable('trashed', 'only')
        ->callTableBulkAction('restore', $mailTemplates)
        ->assertNotified();
});

test('bulk force delete shows notification', function () {
    $mailTemplates = MailTemplate::factory()->count(3)->create();
    foreach ($mailTemplates as $mailTemplate) {
        $mailTemplate->delete();
    }

    livewire(MailTemplateResource\Pages\ListMailTemplates::class)
        ->filterTable('trashed', 'only')
        ->callTableBulkAction('forceDelete', $mailTemplates)
        ->assertNotified();
});

test('mail templates are sorted by updated_at descending by default', function () {
    $older = MailTemplate::factory()->create(['updated_at' => now()->subDays(2)]);
    $newer = MailTemplate::factory()->create(['updated_at' => now()->subDay()]);

    livewire(MailTemplateResource\Pages\ListMailTemplates::class)
        ->assertCanSeeTableRecords([$newer, $older], inOrder: true);
});

test('can sort mail templates by name', function () {
    $template1 = MailTemplate::factory()->create(['name' => 'A Template']);
    $template2 = MailTemplate::factory()->create(['name' => 'B Template']);

    livewire(MailTemplateResource\Pages\ListMailTemplates::class)
        ->sortTable('name')
        ->assertCanSeeTableRecords([$template1, $template2], inOrder: true);
});

test('can sort mail templates by category', function () {
    $documentsTemplate = MailTemplate::factory()->documentExpiring()->create();
    $incidentsTemplate = MailTemplate::factory()->incidentCreated()->create();

    livewire(MailTemplateResource\Pages\ListMailTemplates::class)
        ->sortTable('category')
        ->assertCanSeeTableRecords([$documentsTemplate, $incidentsTemplate], inOrder: true);
});

test('can sort mail templates by mailable', function () {
    $template1 = MailTemplate::factory()->documentExpiring()->create();
    $template2 = MailTemplate::factory()->incidentCreated()->create();

    livewire(MailTemplateResource\Pages\ListMailTemplates::class)
        ->sortTable('mailable')
        ->assertSuccessful();
});

test('can sort mail templates by updated_at', function () {
    $template1 = MailTemplate::factory()->create(['updated_at' => now()->subDays(2)]);
    $template2 = MailTemplate::factory()->create(['updated_at' => now()->subDay()]);

    livewire(MailTemplateResource\Pages\ListMailTemplates::class)
        ->sortTable('updated_at')
        ->assertCanSeeTableRecords([$template1, $template2], inOrder: true);
});

test('name has max length validation', function () {
    livewire(MailTemplateResource\Pages\CreateMailTemplate::class)
        ->fillForm([
            'name' => str_repeat('a', 256),
            'category' => 'incidents',
            'mailable' => 'App\\Mail\\IncidentCreatedMail',
            'subject' => 'Test Subject',
            'html_template' => '<p>Content</p>',
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'max']);
});

test('subject has max length validation', function () {
    livewire(MailTemplateResource\Pages\CreateMailTemplate::class)
        ->fillForm([
            'name' => 'Test Template',
            'category' => 'incidents',
            'mailable' => 'App\\Mail\\IncidentCreatedMail',
            'subject' => str_repeat('a', 256),
            'html_template' => '<p>Content</p>',
        ])
        ->call('create')
        ->assertHasFormErrors(['subject' => 'max']);
});

test('can update name', function () {
    $mailTemplate = MailTemplate::factory()->create(['name' => 'Old Name']);

    livewire(MailTemplateResource\Pages\EditMailTemplate::class, ['record' => $mailTemplate->id])
        ->fillForm([
            'name' => 'New Name',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('mail_templates', [
        'id' => $mailTemplate->id,
        'name' => 'New Name',
    ]);
});

test('can update category', function () {
    $mailTemplate = MailTemplate::factory()->create(['category' => 'incidents']);

    livewire(MailTemplateResource\Pages\EditMailTemplate::class, ['record' => $mailTemplate->id])
        ->fillForm([
            'category' => 'documents',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('mail_templates', [
        'id' => $mailTemplate->id,
        'category' => 'documents',
    ]);
});

test('can update mailable', function () {
    $mailTemplate = MailTemplate::factory()->incidentCreated()->create();

    livewire(MailTemplateResource\Pages\EditMailTemplate::class, ['record' => $mailTemplate->id])
        ->fillForm([
            'mailable' => 'App\\Mail\\DocumentUploadedMail',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('mail_templates', [
        'id' => $mailTemplate->id,
        'mailable' => 'App\\Mail\\DocumentUploadedMail',
    ]);
});

test('can update html_template', function () {
    $mailTemplate = MailTemplate::factory()->create(['html_template' => '<p>Old content</p>']);

    livewire(MailTemplateResource\Pages\EditMailTemplate::class, ['record' => $mailTemplate->id])
        ->fillForm([
            'html_template' => '<p>New content</p>',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('mail_templates', [
        'id' => $mailTemplate->id,
        'html_template' => '<p>New content</p>',
    ]);
});

test('can update text_template', function () {
    $mailTemplate = MailTemplate::factory()->create(['text_template' => 'Old text']);

    livewire(MailTemplateResource\Pages\EditMailTemplate::class, ['record' => $mailTemplate->id])
        ->fillForm([
            'text_template' => 'New text',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('mail_templates', [
        'id' => $mailTemplate->id,
        'text_template' => 'New text',
    ]);
});

test('can clear text_template to make it null', function () {
    $mailTemplate = MailTemplate::factory()->create(['text_template' => 'Some text']);

    livewire(MailTemplateResource\Pages\EditMailTemplate::class, ['record' => $mailTemplate->id])
        ->fillForm([
            'text_template' => null,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $mailTemplate->refresh();
    expect($mailTemplate->text_template)->toBeNull();
});

test('category filter shows all category options', function () {
    livewire(MailTemplateResource\Pages\ListMailTemplates::class)
        ->assertSuccessful();
});

test('mailable select shows all available mailables', function () {
    livewire(MailTemplateResource\Pages\CreateMailTemplate::class)
        ->assertFormFieldExists('mailable');
});

test('preview action shows modal', function () {
    $mailTemplate = MailTemplate::factory()->create([
        'name' => 'Test Preview',
        'html_template' => '<p>Preview content</p>',
    ]);

    livewire(MailTemplateResource\Pages\ListMailTemplates::class)
        ->mountTableAction('preview', $mailTemplate)
        ->assertSuccessful();
});
