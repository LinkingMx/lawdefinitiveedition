<?php

declare(strict_types=1);

use App\Models\Branch;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

test('document model uses HasFactory trait', function () {
    expect(Document::class)
        ->toHaveMethod('factory');
});

test('document model uses SoftDeletes trait', function () {
    $traits = class_uses(Document::class);

    expect($traits)->toHaveKey(SoftDeletes::class);
});

test('document has fillable attributes', function () {
    $document = new Document;

    $fillable = $document->getFillable();

    expect($fillable)->toBe([
        'document_type_id',
        'branch_id',
        'description',
        'expires_at',
        'file_path',
        'original_filename',
        'file_size',
        'mime_type',
        'uploaded_by',
    ]);
});

test('document casts expires_at to date', function () {
    $document = Document::factory()->create([
        'expires_at' => '2025-12-31',
    ]);

    expect($document->expires_at)
        ->toBeInstanceOf(\Illuminate\Support\Carbon::class)
        ->and($document->expires_at->format('Y-m-d'))->toBe('2025-12-31');
});

test('document casts created_at to datetime', function () {
    $document = Document::factory()->create();

    expect($document->created_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('document casts updated_at to datetime', function () {
    $document = Document::factory()->create();

    expect($document->updated_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('document casts deleted_at to datetime', function () {
    $document = Document::factory()->create();
    $document->delete();

    expect($document->deleted_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('document has documentType relationship', function () {
    $document = new Document;

    expect($document->documentType())
        ->toBeInstanceOf(BelongsTo::class);
});

test('document belongs to documentType', function () {
    $documentType = DocumentType::factory()->create(['name' => 'Contract']);
    $document = Document::factory()->create(['document_type_id' => $documentType->id]);

    expect($document->documentType)
        ->toBeInstanceOf(DocumentType::class)
        ->and($document->documentType->id)->toBe($documentType->id)
        ->and($document->documentType->name)->toBe('Contract');
});

test('document has branch relationship', function () {
    $document = new Document;

    expect($document->branch())
        ->toBeInstanceOf(BelongsTo::class);
});

test('document belongs to branch', function () {
    $branch = Branch::factory()->create(['name' => 'Main Office']);
    $document = Document::factory()->create(['branch_id' => $branch->id]);

    expect($document->branch)
        ->toBeInstanceOf(Branch::class)
        ->and($document->branch->id)->toBe($branch->id)
        ->and($document->branch->name)->toBe('Main Office');
});

test('document has uploadedBy relationship', function () {
    $document = new Document;

    expect($document->uploadedBy())
        ->toBeInstanceOf(BelongsTo::class);
});

test('document belongs to uploadedBy user', function () {
    $user = User::factory()->create(['name' => 'John Doe']);
    $document = Document::factory()->create(['uploaded_by' => $user->id]);

    expect($document->uploadedBy)
        ->toBeInstanceOf(User::class)
        ->and($document->uploadedBy->id)->toBe($user->id)
        ->and($document->uploadedBy->name)->toBe('John Doe');
});

test('isExpired returns false when expires_at is null', function () {
    $document = Document::factory()->create(['expires_at' => null]);

    expect($document->isExpired())->toBeFalse();
});

test('isExpired returns true when expires_at is in the past', function () {
    $document = Document::factory()->expired()->create();

    expect($document->isExpired())->toBeTrue();
});

test('isExpired returns false when expires_at is in the future', function () {
    $document = Document::factory()->create(['expires_at' => now()->addDays(30)]);

    expect($document->isExpired())->toBeFalse();
});

test('factory creates valid document', function () {
    $document = Document::factory()->create();

    expect($document)->toBeInstanceOf(Document::class)
        ->and($document->document_type_id)->toBeInt()
        ->and($document->branch_id)->toBeInt()
        ->and($document->file_path)->toBeString()
        ->and($document->original_filename)->toBeString()
        ->and($document->file_size)->toBeInt()
        ->and($document->mime_type)->toBeString()
        ->and($document->uploaded_by)->toBeInt();
});

test('factory creates document with valid document_type relationship', function () {
    $document = Document::factory()->create();

    expect($document->documentType)->toBeInstanceOf(DocumentType::class);
});

test('factory creates document with valid branch relationship', function () {
    $document = Document::factory()->create();

    expect($document->branch)->toBeInstanceOf(Branch::class);
});

test('factory creates document with valid uploadedBy relationship', function () {
    $document = Document::factory()->create();

    expect($document->uploadedBy)->toBeInstanceOf(User::class);
});

test('factory expired state creates expired document', function () {
    $document = Document::factory()->expired()->create();

    expect($document->expires_at)->not->toBeNull()
        ->and($document->isExpired())->toBeTrue();
});

test('factory noExpiration state creates document without expiration', function () {
    $document = Document::factory()->noExpiration()->create();

    expect($document->expires_at)->toBeNull()
        ->and($document->isExpired())->toBeFalse();
});

test('factory withDescription state creates document with description', function () {
    $document = Document::factory()->withDescription()->create();

    expect($document->description)
        ->not->toBeNull()
        ->toBeString()
        ->and(strlen($document->description))->toBeGreaterThan(50);
});

test('factory withoutDescription state creates document without description', function () {
    $document = Document::factory()->withoutDescription()->create();

    expect($document->description)->toBeNull();
});

test('document has timestamps', function () {
    $document = Document::factory()->create();

    expect($document->created_at)->not->toBeNull()
        ->and($document->updated_at)->not->toBeNull();
});

test('document can be soft deleted', function () {
    $document = Document::factory()->create();

    $document->delete();

    expect($document->deleted_at)->not->toBeNull()
        ->and(Document::withTrashed()->find($document->id))->not->toBeNull();
});

test('document can be restored after soft delete', function () {
    $document = Document::factory()->create();
    $document->delete();

    $document->restore();

    expect($document->deleted_at)->toBeNull()
        ->and(Document::find($document->id))->not->toBeNull();
});

test('document can be force deleted', function () {
    $document = Document::factory()->create();
    $documentId = $document->id;

    $document->forceDelete();

    expect(Document::withTrashed()->find($documentId))->toBeNull();
});

test('document updates updated_at timestamp on save', function () {
    $document = Document::factory()->create();
    $originalUpdatedAt = $document->updated_at;

    sleep(1);
    $document->description = 'Updated description';
    $document->save();

    expect($document->updated_at->isAfter($originalUpdatedAt))->toBeTrue();
});

test('document preserves created_at timestamp on update', function () {
    $document = Document::factory()->create();
    $originalCreatedAt = $document->created_at;

    $document->description = 'Updated description';
    $document->save();

    expect($document->created_at->equalTo($originalCreatedAt))->toBeTrue();
});

test('document allows null description', function () {
    $document = Document::factory()->create(['description' => null]);

    expect($document->description)->toBeNull();
});

test('document allows null expires_at', function () {
    $document = Document::factory()->create(['expires_at' => null]);

    expect($document->expires_at)->toBeNull();
});

test('document file_size is stored as integer', function () {
    $document = Document::factory()->create(['file_size' => 1024000]);

    expect($document->file_size)
        ->toBeInt()
        ->toBe(1024000);
});

test('document mime_type is stored correctly', function () {
    $document = Document::factory()->create(['mime_type' => 'application/pdf']);

    expect($document->mime_type)->toBe('application/pdf');
});

test('document original_filename is stored correctly', function () {
    $document = Document::factory()->create(['original_filename' => 'test-document.pdf']);

    expect($document->original_filename)->toBe('test-document.pdf');
});

test('document file_path is stored correctly', function () {
    $document = Document::factory()->create(['file_path' => 'documents/test.pdf']);

    expect($document->file_path)->toBe('documents/test.pdf');
});
