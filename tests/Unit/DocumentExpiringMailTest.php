<?php

declare(strict_types=1);

use App\Mail\DocumentExpiringMail;
use App\Models\Branch;
use App\Models\Document;
use Spatie\MailTemplates\TemplateMailable;

test('document expiring mail extends TemplateMailable', function () {
    expect(is_subclass_of(DocumentExpiringMail::class, TemplateMailable::class))
        ->toBeTrue();
});

test('document expiring mail has public document property', function () {
    $document = Document::factory()->create(['expires_at' => now()->addDays(5)]);

    $mail = new DocumentExpiringMail($document);

    expect($mail)->toHaveProperty('document')
        ->and($mail->document)->toBeInstanceOf(Document::class)
        ->and($mail->document->id)->toBe($document->id);
});

test('document expiring mail has public days_until_expiry property', function () {
    $document = Document::factory()->create(['expires_at' => now()->addDays(10)]);

    $mail = new DocumentExpiringMail($document);

    expect($mail)->toHaveProperty('days_until_expiry')
        ->and($mail->days_until_expiry)->toBeInt()
        ->and($mail->days_until_expiry)->toBeGreaterThanOrEqual(9)
        ->and($mail->days_until_expiry)->toBeLessThanOrEqual(10);
});

test('document expiring mail calculates days_until_expiry correctly', function () {
    $expiresAt = now()->addDays(7);
    $document = Document::factory()->create(['expires_at' => $expiresAt]);

    $mail = new DocumentExpiringMail($document);

    $expectedDays = (int) now()->diffInDays($document->expires_at);

    expect($mail->days_until_expiry)->toBe($expectedDays);
});

test('document expiring mail receives document in constructor', function () {
    $document = Document::factory()->create([
        'original_filename' => 'contract.pdf',
        'expires_at' => now()->addDays(3),
    ]);

    $mail = new DocumentExpiringMail($document);

    expect($mail->document->original_filename)->toBe('contract.pdf')
        ->and($mail->document->expires_at)->not->toBeNull();
});

test('document expiring mail document has branch relationship', function () {
    $branch = Branch::factory()->create(['name' => 'Legal Department']);
    $document = Document::factory()->create([
        'branch_id' => $branch->id,
        'expires_at' => now()->addDays(5),
    ]);

    $mail = new DocumentExpiringMail($document);

    expect($mail->document->branch)->toBeInstanceOf(Branch::class)
        ->and($mail->document->branch->name)->toBe('Legal Department');
});

test('document expiring mail handles documents expiring tomorrow', function () {
    $document = Document::factory()->create(['expires_at' => now()->addDay()]);

    $mail = new DocumentExpiringMail($document);

    expect($mail->days_until_expiry)->toBeGreaterThanOrEqual(0)
        ->and($mail->days_until_expiry)->toBeLessThanOrEqual(1);
});

test('document expiring mail handles documents expiring today', function () {
    $document = Document::factory()->create(['expires_at' => now()->endOfDay()]);

    $mail = new DocumentExpiringMail($document);

    expect($mail->days_until_expiry)->toBe(0);
});
