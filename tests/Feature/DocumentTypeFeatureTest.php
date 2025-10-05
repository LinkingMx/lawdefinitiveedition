<?php

declare(strict_types=1);

use App\Models\DocumentType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('can create document type with valid data', function () {
    $documentType = DocumentType::create([
        'name' => 'Contract',
        'description' => 'Legal contract documents',
    ]);

    expect($documentType)
        ->toBeInstanceOf(DocumentType::class)
        ->name->toBe('Contract')
        ->description->toBe('Legal contract documents');

    $this->assertDatabaseHas('document_types', [
        'name' => 'Contract',
        'description' => 'Legal contract documents',
    ]);
});

test('can create document type without description', function () {
    $documentType = DocumentType::create([
        'name' => 'Invoice',
    ]);

    expect($documentType)
        ->toBeInstanceOf(DocumentType::class)
        ->name->toBe('Invoice')
        ->description->toBeNull();

    $this->assertDatabaseHas('document_types', [
        'name' => 'Invoice',
        'description' => null,
    ]);
});

test('name field is required', function () {
    expect(fn () => DocumentType::create([
        'description' => 'Test description',
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

test('name must be unique', function () {
    DocumentType::create([
        'name' => 'Agreement',
        'description' => 'First agreement',
    ]);

    expect(fn () => DocumentType::create([
        'name' => 'Agreement',
        'description' => 'Second agreement',
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

test('can update document type', function () {
    $documentType = DocumentType::factory()->create([
        'name' => 'Original Name',
        'description' => 'Original description',
    ]);

    $documentType->update([
        'name' => 'Updated Name',
        'description' => 'Updated description',
    ]);

    expect($documentType->fresh())
        ->name->toBe('Updated Name')
        ->description->toBe('Updated description');

    $this->assertDatabaseHas('document_types', [
        'id' => $documentType->id,
        'name' => 'Updated Name',
        'description' => 'Updated description',
    ]);
});

test('can soft delete document type', function () {
    $documentType = DocumentType::factory()->create();

    $documentType->delete();

    expect($documentType->fresh())
        ->deleted_at->not->toBeNull();

    $this->assertSoftDeleted('document_types', [
        'id' => $documentType->id,
    ]);
});

test('can restore soft deleted document type', function () {
    $documentType = DocumentType::factory()->deleted()->create();

    expect($documentType->deleted_at)->not->toBeNull();

    $documentType->restore();

    expect($documentType->fresh())
        ->deleted_at->toBeNull();

    $this->assertDatabaseHas('document_types', [
        'id' => $documentType->id,
        'deleted_at' => null,
    ]);
});

test('can force delete document type', function () {
    $documentType = DocumentType::factory()->create();
    $id = $documentType->id;

    $documentType->forceDelete();

    $this->assertDatabaseMissing('document_types', [
        'id' => $id,
    ]);
});

test('can retrieve all document types excluding soft deleted', function () {
    DocumentType::factory()->count(3)->create();
    DocumentType::factory()->deleted()->count(2)->create();

    $documentTypes = DocumentType::all();

    expect($documentTypes)->toHaveCount(3);
});

test('can retrieve all document types including soft deleted', function () {
    DocumentType::factory()->count(3)->create();
    DocumentType::factory()->deleted()->count(2)->create();

    $documentTypes = DocumentType::withTrashed()->get();

    expect($documentTypes)->toHaveCount(5);
});

test('can retrieve only soft deleted document types', function () {
    DocumentType::factory()->count(3)->create();
    DocumentType::factory()->deleted()->count(2)->create();

    $documentTypes = DocumentType::onlyTrashed()->get();

    expect($documentTypes)->toHaveCount(2);
});

test('can search document types by name', function () {
    DocumentType::factory()->create(['name' => 'Contract']);
    DocumentType::factory()->create(['name' => 'Invoice']);
    DocumentType::factory()->create(['name' => 'Agreement']);

    $results = DocumentType::where('name', 'like', '%Contract%')->get();

    expect($results)
        ->toHaveCount(1)
        ->first()->name->toBe('Contract');
});

test('can sort document types by name', function () {
    DocumentType::factory()->create(['name' => 'Zebra']);
    DocumentType::factory()->create(['name' => 'Alpha']);
    DocumentType::factory()->create(['name' => 'Beta']);

    $results = DocumentType::orderBy('name', 'asc')->get();

    expect($results)
        ->toHaveCount(3)
        ->sequence(
            fn ($type) => $type->name->toBe('Alpha'),
            fn ($type) => $type->name->toBe('Beta'),
            fn ($type) => $type->name->toBe('Zebra')
        );
});

test('timestamps are automatically managed', function () {
    $documentType = DocumentType::factory()->create();

    expect($documentType)
        ->created_at->not->toBeNull()
        ->updated_at->not->toBeNull();

    $originalUpdatedAt = $documentType->updated_at;

    sleep(1);

    $documentType->update(['name' => 'Updated Name']);

    expect($documentType->fresh())
        ->updated_at->not->toBe($originalUpdatedAt);
});
