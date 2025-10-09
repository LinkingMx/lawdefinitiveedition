<?php

declare(strict_types=1);

use App\Models\MailTemplate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

test('mail template model uses HasFactory trait', function () {
    expect(class_uses(MailTemplate::class))
        ->toHaveKey(HasFactory::class);
});

test('mail template model uses SoftDeletes trait', function () {
    expect(class_uses(MailTemplate::class))
        ->toHaveKey(SoftDeletes::class);
});

test('mail template model has correct fillable attributes', function () {
    $mailTemplate = new MailTemplate;

    expect($mailTemplate->getFillable())->toBe([
        'name',
        'category',
        'mailable',
        'subject',
        'html_template',
        'text_template',
        'available_variables',
    ]);
});

test('mail template model casts available_variables to array', function () {
    $mailTemplate = new MailTemplate;

    expect($mailTemplate->getCasts())->toHaveKey('available_variables')
        ->and($mailTemplate->getCasts()['available_variables'])->toBe('array');
});

test('can create mail template with factory', function () {
    $mailTemplate = MailTemplate::factory()->create();

    expect($mailTemplate)->toBeInstanceOf(MailTemplate::class)
        ->and($mailTemplate->name)->toBeString()
        ->and($mailTemplate->category)->toBeString()
        ->and($mailTemplate->mailable)->toBeString()
        ->and($mailTemplate->subject)->toBeString()
        ->and($mailTemplate->html_template)->toBeString()
        ->and($mailTemplate->available_variables)->toBeArray();
});

test('can soft delete mail template', function () {
    $mailTemplate = MailTemplate::factory()->create();

    $mailTemplate->delete();

    expect($mailTemplate->trashed())->toBeTrue()
        ->and(MailTemplate::withTrashed()->find($mailTemplate->id))->not->toBeNull();
});

test('can restore soft deleted mail template', function () {
    $mailTemplate = MailTemplate::factory()->create();
    $mailTemplate->delete();

    $mailTemplate->restore();

    expect($mailTemplate->trashed())->toBeFalse()
        ->and(MailTemplate::find($mailTemplate->id))->not->toBeNull();
});

test('can force delete mail template', function () {
    $mailTemplate = MailTemplate::factory()->create();
    $id = $mailTemplate->id;

    $mailTemplate->forceDelete();

    expect(MailTemplate::withTrashed()->find($id))->toBeNull();
});

test('factory creates incident created mail template state', function () {
    $mailTemplate = MailTemplate::factory()->incidentCreated()->create();

    expect($mailTemplate->category)->toBe('incidents')
        ->and($mailTemplate->mailable)->toBe('App\\Mail\\IncidentCreatedMail')
        ->and($mailTemplate->available_variables)->toHaveKey('incident.title')
        ->and($mailTemplate->available_variables)->toHaveKey('reporter.name');
});

test('factory creates incident comment mail template state', function () {
    $mailTemplate = MailTemplate::factory()->incidentComment()->create();

    expect($mailTemplate->category)->toBe('incidents')
        ->and($mailTemplate->mailable)->toBe('App\\Mail\\IncidentCommentMail')
        ->and($mailTemplate->available_variables)->toHaveKey('comment.comment')
        ->and($mailTemplate->available_variables)->toHaveKey('user.name');
});

test('factory creates document expiring mail template state', function () {
    $mailTemplate = MailTemplate::factory()->documentExpiring()->create();

    expect($mailTemplate->category)->toBe('documents')
        ->and($mailTemplate->mailable)->toBe('App\\Mail\\DocumentExpiringMail')
        ->and($mailTemplate->available_variables)->toHaveKey('document.original_filename')
        ->and($mailTemplate->available_variables)->toHaveKey('days_until_expiry');
});

test('factory creates document uploaded mail template state', function () {
    $mailTemplate = MailTemplate::factory()->documentUploaded()->create();

    expect($mailTemplate->category)->toBe('documents')
        ->and($mailTemplate->mailable)->toBe('App\\Mail\\DocumentUploadedMail')
        ->and($mailTemplate->available_variables)->toHaveKey('uploader.name')
        ->and($mailTemplate->available_variables)->toHaveKey('upload_date');
});

test('available_variables is stored as json in database', function () {
    $variables = [
        'incident.title' => 'Título del incidente',
        'reporter.name' => 'Nombre del reportador',
    ];

    $mailTemplate = MailTemplate::factory()->create([
        'available_variables' => $variables,
    ]);

    expect($mailTemplate->available_variables)->toBe($variables);

    $mailTemplate->refresh();

    expect($mailTemplate->available_variables)->toBe($variables);
});

test('text_template is nullable', function () {
    $mailTemplate = MailTemplate::factory()->create([
        'text_template' => null,
    ]);

    expect($mailTemplate->text_template)->toBeNull();
});

test('mail template extends spatie base mail template', function () {
    expect(is_subclass_of(MailTemplate::class, \Spatie\MailTemplates\Models\MailTemplate::class))
        ->toBeTrue();
});
