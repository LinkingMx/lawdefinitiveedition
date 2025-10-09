<?php

declare(strict_types=1);

use App\Mail\IncidentCreatedMail;
use App\Models\Branch;
use App\Models\Incident;
use App\Models\User;
use Spatie\MailTemplates\TemplateMailable;

test('incident created mail extends TemplateMailable', function () {
    expect(is_subclass_of(IncidentCreatedMail::class, TemplateMailable::class))
        ->toBeTrue();
});

test('incident created mail has public incident property', function () {
    $incident = Incident::factory()->create();

    $mail = new IncidentCreatedMail($incident);

    expect($mail)->toHaveProperty('incident')
        ->and($mail->incident)->toBeInstanceOf(Incident::class)
        ->and($mail->incident->id)->toBe($incident->id);
});

test('incident created mail receives incident in constructor', function () {
    $branch = Branch::factory()->create(['name' => 'Main Office']);
    $reporter = User::factory()->create(['name' => 'John Doe']);

    $incident = Incident::factory()->create([
        'title' => 'Test Incident',
        'priority' => 'high',
        'description' => 'Test description',
        'branch_id' => $branch->id,
        'user_id' => $reporter->id,
    ]);

    $mail = new IncidentCreatedMail($incident);

    expect($mail->incident->title)->toBe('Test Incident')
        ->and($mail->incident->priority)->toBe('high')
        ->and($mail->incident->description)->toBe('Test description');
});

test('incident created mail incident has branch relationship', function () {
    $branch = Branch::factory()->create(['name' => 'Main Office']);
    $incident = Incident::factory()->create(['branch_id' => $branch->id]);

    $mail = new IncidentCreatedMail($incident);

    expect($mail->incident->branch)->toBeInstanceOf(Branch::class)
        ->and($mail->incident->branch->name)->toBe('Main Office');
});

test('incident created mail incident has reporter relationship', function () {
    $reporter = User::factory()->create(['name' => 'John Doe']);
    $incident = Incident::factory()->create(['user_id' => $reporter->id]);

    $mail = new IncidentCreatedMail($incident);

    expect($mail->incident->reporter)->toBeInstanceOf(User::class)
        ->and($mail->incident->reporter->name)->toBe('John Doe');
});
