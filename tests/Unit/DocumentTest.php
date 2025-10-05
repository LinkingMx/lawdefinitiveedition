<?php

declare(strict_types=1);

use App\Models\Branch;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

test('document uses expected traits', function () {
    $uses = class_uses(Document::class);

    expect($uses)->toHaveKey(SoftDeletes::class);
});

test('document has correct fillable attributes', function () {
    $document = new Document;
    $fillable = $document->getFillable();

    expect($fillable)->toContain('document_type_id')
        ->and($fillable)->toContain('branch_id')
        ->and($fillable)->toContain('description')
        ->and($fillable)->toContain('expires_at')
        ->and($fillable)->toContain('file_path')
        ->and($fillable)->toContain('original_filename')
        ->and($fillable)->toContain('file_size')
        ->and($fillable)->toContain('mime_type')
        ->and($fillable)->toContain('uploaded_by');
});

test('document casts attributes correctly', function () {
    $document = new Document;
    $casts = $document->getCasts();

    expect($casts)->toHaveKey('expires_at', 'date')
        ->and($casts)->toHaveKey('created_at', 'datetime')
        ->and($casts)->toHaveKey('updated_at', 'datetime')
        ->and($casts)->toHaveKey('deleted_at', 'datetime');
});

test('document belongs to document type', function () {
    $document = Document::factory()->create();

    expect($document->documentType)->toBeInstanceOf(DocumentType::class);
});

test('document belongs to branch', function () {
    $document = Document::factory()->create();

    expect($document->branch)->toBeInstanceOf(Branch::class);
});

test('document belongs to user who uploaded it', function () {
    $document = Document::factory()->create();

    expect($document->uploadedBy)->toBeInstanceOf(User::class);
});

test('document can be soft deleted', function () {
    $document = Document::factory()->create();

    $document->delete();

    expect($document->trashed())->toBeTrue();
});

test('document can be restored after soft delete', function () {
    $document = Document::factory()->create();
    $document->delete();

    $document->restore();

    expect($document->trashed())->toBeFalse();
});

test('is_expired returns false when expires_at is null', function () {
    $document = Document::factory()->noExpiration()->create();

    expect($document->isExpired())->toBeFalse();
});

test('is_expired returns true when expires_at is in the past', function () {
    $document = Document::factory()->expired()->create();

    expect($document->isExpired())->toBeTrue();
});

test('is_expired returns false when expires_at is in the future', function () {
    $document = Document::factory()->create([
        'expires_at' => now()->addDays(30),
    ]);

    expect($document->isExpired())->toBeFalse();
});

test('document factory creates valid document', function () {
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

test('document factory expired state works', function () {
    $document = Document::factory()->expired()->create();

    expect($document->expires_at)->not->toBeNull()
        ->and($document->isExpired())->toBeTrue();
});

test('document factory no_expiration state works', function () {
    $document = Document::factory()->noExpiration()->create();

    expect($document->expires_at)->toBeNull();
});

test('document factory with_description state works', function () {
    $document = Document::factory()->withDescription()->create();

    expect($document->description)->not->toBeNull()
        ->and($document->description)->toBeString();
});

test('document factory without_description state works', function () {
    $document = Document::factory()->withoutDescription()->create();

    expect($document->description)->toBeNull();
});
