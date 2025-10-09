<?php

declare(strict_types=1);

use App\Mail\DocumentUploadedMail;
use App\Models\Branch;
use App\Models\Document;
use App\Models\User;
use Spatie\MailTemplates\TemplateMailable;

test('document uploaded mail extends TemplateMailable', function () {
    expect(is_subclass_of(DocumentUploadedMail::class, TemplateMailable::class))
        ->toBeTrue();
});

test('document uploaded mail has public document property', function () {
    $document = Document::factory()->create();

    $mail = new DocumentUploadedMail($document);

    expect($mail)->toHaveProperty('document')
        ->and($mail->document)->toBeInstanceOf(Document::class)
        ->and($mail->document->id)->toBe($document->id);
});

test('document uploaded mail has public upload_date property', function () {
    $document = Document::factory()->create();

    $mail = new DocumentUploadedMail($document);

    expect($mail)->toHaveProperty('upload_date')
        ->and($mail->upload_date)->toBeString();
});

test('document uploaded mail has public uploader property', function () {
    $uploader = User::factory()->create(['name' => 'Upload User']);
    $document = Document::factory()->create(['uploaded_by' => $uploader->id]);

    $mail = new DocumentUploadedMail($document);

    expect($mail)->toHaveProperty('uploader')
        ->and($mail->uploader)->toBeInstanceOf(User::class)
        ->and($mail->uploader->name)->toBe('Upload User');
});

test('document uploaded mail formats upload_date correctly', function () {
    $createdAt = now()->setDateTime(2025, 10, 8, 14, 30, 0);
    $document = Document::factory()->create(['created_at' => $createdAt]);

    $mail = new DocumentUploadedMail($document);

    expect($mail->upload_date)->toBe('08/10/2025 14:30');
});

test('document uploaded mail receives document in constructor', function () {
    $document = Document::factory()->create(['original_filename' => 'report.pdf']);

    $mail = new DocumentUploadedMail($document);

    expect($mail->document->original_filename)->toBe('report.pdf');
});

test('document uploaded mail document has branch relationship', function () {
    $branch = Branch::factory()->create(['name' => 'Finance Department']);
    $document = Document::factory()->create(['branch_id' => $branch->id]);

    $mail = new DocumentUploadedMail($document);

    expect($mail->document->branch)->toBeInstanceOf(Branch::class)
        ->and($mail->document->branch->name)->toBe('Finance Department');
});

test('document uploaded mail uploader is loaded from uploadedBy relationship', function () {
    $uploader = User::factory()->create(['name' => 'Document Uploader']);
    $document = Document::factory()->create(['uploaded_by' => $uploader->id]);

    $mail = new DocumentUploadedMail($document);

    expect($mail->uploader->id)->toBe($uploader->id)
        ->and($mail->uploader->name)->toBe('Document Uploader');
});

test('document uploaded mail initializes all properties in constructor', function () {
    $uploader = User::factory()->create(['name' => 'Test Uploader']);
    $branch = Branch::factory()->create(['name' => 'Test Branch']);
    $document = Document::factory()->create([
        'original_filename' => 'test-doc.pdf',
        'uploaded_by' => $uploader->id,
        'branch_id' => $branch->id,
    ]);

    $mail = new DocumentUploadedMail($document);

    expect($mail->document->original_filename)->toBe('test-doc.pdf')
        ->and($mail->uploader->name)->toBe('Test Uploader')
        ->and($mail->upload_date)->toBeString()
        ->and($mail->document->branch->name)->toBe('Test Branch');
});
