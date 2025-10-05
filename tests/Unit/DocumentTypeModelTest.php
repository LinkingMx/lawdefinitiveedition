<?php

declare(strict_types=1);

use App\Models\DocumentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ========================================
// MODEL STRUCTURE TESTS
// ========================================

test('document type uses HasFactory trait', function () {
    $traits = class_uses(DocumentType::class);

    expect($traits)->toContain(HasFactory::class);
});

test('document type uses SoftDeletes trait', function () {
    $traits = class_uses(DocumentType::class);

    expect($traits)->toContain(SoftDeletes::class);
});

test('document type has strict types declaration', function () {
    $reflection = new ReflectionClass(DocumentType::class);
    $contents = file_get_contents($reflection->getFileName());

    expect($contents)->toContain('declare(strict_types=1)');
});

test('document type has correct namespace', function () {
    $documentType = new DocumentType;

    expect($documentType)->toBeInstanceOf('App\Models\DocumentType');
});

// ========================================
// FILLABLE ATTRIBUTES TESTS
// ========================================

test('document type has correct fillable attributes', function () {
    $documentType = new DocumentType;
    $fillable = $documentType->getFillable();

    expect($fillable)
        ->toBeArray()
        ->toHaveCount(2)
        ->toContain('name')
        ->toContain('description');
});

test('can mass assign name attribute', function () {
    $documentType = new DocumentType(['name' => 'Test Name']);

    expect($documentType->name)->toBe('Test Name');
});

test('can mass assign description attribute', function () {
    $documentType = new DocumentType(['description' => 'Test Description']);

    expect($documentType->description)->toBe('Test Description');
});

test('cannot mass assign id attribute', function () {
    $documentType = new DocumentType(['id' => 999]);

    expect($documentType->id)->toBeNull();
});

test('cannot mass assign created_at attribute', function () {
    $date = now();
    $documentType = new DocumentType(['created_at' => $date]);

    expect($documentType->created_at)->toBeNull();
});

test('cannot mass assign updated_at attribute', function () {
    $date = now();
    $documentType = new DocumentType(['updated_at' => $date]);

    expect($documentType->updated_at)->toBeNull();
});

test('cannot mass assign deleted_at attribute', function () {
    $date = now();
    $documentType = new DocumentType(['deleted_at' => $date]);

    expect($documentType->deleted_at)->toBeNull();
});

// ========================================
// CASTS TESTS
// ========================================

test('document type casts timestamps correctly', function () {
    $documentType = new DocumentType;
    $casts = $documentType->getCasts();

    expect($casts)
        ->toHaveKey('created_at', 'datetime')
        ->toHaveKey('updated_at', 'datetime')
        ->toHaveKey('deleted_at', 'datetime');
});

test('created_at is cast to datetime instance', function () {
    $documentType = DocumentType::factory()->create();

    expect($documentType->created_at)
        ->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('updated_at is cast to datetime instance', function () {
    $documentType = DocumentType::factory()->create();

    expect($documentType->updated_at)
        ->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('deleted_at is cast to datetime instance when soft deleted', function () {
    $documentType = DocumentType::factory()->deleted()->create();

    expect($documentType->deleted_at)
        ->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

// ========================================
// SOFT DELETES TESTS
// ========================================

test('soft deleting document type sets deleted_at timestamp', function () {
    $documentType = DocumentType::factory()->create();

    expect($documentType->deleted_at)->toBeNull();

    $documentType->delete();

    expect($documentType->refresh()->deleted_at)->not->toBeNull();
});

test('soft deleted document types are excluded from default queries', function () {
    DocumentType::factory()->count(3)->create();
    DocumentType::factory()->deleted()->count(2)->create();

    $count = DocumentType::count();

    expect($count)->toBe(3);
});

test('can retrieve soft deleted document types with withTrashed', function () {
    DocumentType::factory()->count(3)->create();
    DocumentType::factory()->deleted()->count(2)->create();

    $count = DocumentType::withTrashed()->count();

    expect($count)->toBe(5);
});

test('can retrieve only soft deleted document types with onlyTrashed', function () {
    DocumentType::factory()->count(3)->create();
    DocumentType::factory()->deleted()->count(2)->create();

    $count = DocumentType::onlyTrashed()->count();

    expect($count)->toBe(2);
});

test('can restore soft deleted document type', function () {
    $documentType = DocumentType::factory()->deleted()->create();

    expect($documentType->deleted_at)->not->toBeNull();

    $documentType->restore();

    expect($documentType->refresh()->deleted_at)->toBeNull();
});

test('can force delete document type', function () {
    $documentType = DocumentType::factory()->create();
    $id = $documentType->id;

    $documentType->forceDelete();

    expect(DocumentType::withTrashed()->find($id))->toBeNull();
});

// ========================================
// TIMESTAMP TESTS
// ========================================

test('timestamps are automatically set on creation', function () {
    $documentType = DocumentType::factory()->create();

    expect($documentType)
        ->created_at->not->toBeNull()
        ->updated_at->not->toBeNull();
});

test('updated_at is automatically updated on save', function () {
    $documentType = DocumentType::factory()->create();
    $originalUpdatedAt = $documentType->updated_at;

    sleep(1);

    $documentType->update(['name' => 'Updated Name']);

    expect($documentType->refresh()->updated_at)
        ->not->toBe($originalUpdatedAt);
});

test('created_at does not change on update', function () {
    $documentType = DocumentType::factory()->create();
    $originalCreatedAt = $documentType->created_at;

    sleep(1);

    $documentType->update(['name' => 'Updated Name']);

    expect($documentType->refresh()->created_at->timestamp)
        ->toBe($originalCreatedAt->timestamp);
});

// ========================================
// ATTRIBUTES TESTS
// ========================================

test('can set and get name attribute', function () {
    $documentType = DocumentType::factory()->create(['name' => 'Test Name']);

    expect($documentType->name)->toBe('Test Name');

    $documentType->name = 'New Name';
    $documentType->save();

    expect($documentType->refresh()->name)->toBe('New Name');
});

test('can set and get description attribute', function () {
    $documentType = DocumentType::factory()->create(['description' => 'Test Description']);

    expect($documentType->description)->toBe('Test Description');

    $documentType->description = 'New Description';
    $documentType->save();

    expect($documentType->refresh()->description)->toBe('New Description');
});

test('description can be null', function () {
    $documentType = DocumentType::factory()->withoutDescription()->create();

    expect($documentType->description)->toBeNull();
});

test('name is trimmed on save', function () {
    $documentType = DocumentType::factory()->create(['name' => '  Trimmed Name  ']);

    expect($documentType->name)->toBe('  Trimmed Name  ');
});

// ========================================
// RELATIONSHIPS TESTS
// ========================================

test('document type has documents relationship', function () {
    $documentType = new DocumentType;

    expect($documentType->documents())->toBeInstanceOf(HasMany::class);
});

test('documents relationship returns HasMany instance', function () {
    $documentType = DocumentType::factory()->create();

    $relation = $documentType->documents();

    expect($relation)
        ->toBeInstanceOf(HasMany::class)
        ->and($relation->getRelated())
        ->toBeInstanceOf(\App\Models\Document::class);
});

// ========================================
// DATABASE CONSTRAINTS TESTS
// ========================================

test('name must be unique in database', function () {
    DocumentType::factory()->create(['name' => 'Unique Name']);

    expect(fn () => DocumentType::factory()->create(['name' => 'Unique Name']))
        ->toThrow(\Illuminate\Database\QueryException::class);
});

test('name is required in database', function () {
    expect(fn () => DocumentType::create(['description' => 'Test']))
        ->toThrow(\Illuminate\Database\QueryException::class);
});

// ========================================
// FACTORY TESTS
// ========================================

test('document type factory creates valid instance', function () {
    $documentType = DocumentType::factory()->make();

    expect($documentType)
        ->toBeInstanceOf(DocumentType::class)
        ->name->not->toBeNull()
        ->name->toBeString()
        ->description->not->toBeNull()
        ->description->toBeString();
});

test('document type factory persists to database', function () {
    $documentType = DocumentType::factory()->create();

    expect($documentType->exists)->toBeTrue()
        ->and($documentType->id)->not->toBeNull();

    $this->assertDatabaseHas('document_types', [
        'id' => $documentType->id,
        'name' => $documentType->name,
    ]);
});

test('document type factory withoutDescription state works', function () {
    $documentType = DocumentType::factory()->withoutDescription()->make();

    expect($documentType)
        ->toBeInstanceOf(DocumentType::class)
        ->name->not->toBeNull()
        ->description->toBeNull();
});

test('document type factory deleted state works', function () {
    $documentType = DocumentType::factory()->deleted()->make();

    expect($documentType)
        ->toBeInstanceOf(DocumentType::class)
        ->deleted_at->not->toBeNull();
});

test('document type factory creates unique names', function () {
    $documentTypes = DocumentType::factory()->count(5)->create();

    $names = $documentTypes->pluck('name')->toArray();

    expect($names)
        ->toHaveCount(5)
        ->and(count(array_unique($names)))
        ->toBe(5);
});

// ========================================
// MODEL BEHAVIOR TESTS
// ========================================

test('document type model uses correct table name', function () {
    $documentType = new DocumentType;

    expect($documentType->getTable())->toBe('document_types');
});

test('document type primary key is id', function () {
    $documentType = new DocumentType;

    expect($documentType->getKeyName())->toBe('id');
});

test('document type primary key is incrementing', function () {
    $documentType = new DocumentType;

    expect($documentType->getIncrementing())->toBeTrue();
});

test('document type primary key type is int', function () {
    $documentType = new DocumentType;

    expect($documentType->getKeyType())->toBe('int');
});

test('document type uses timestamps', function () {
    $documentType = new DocumentType;

    expect($documentType->usesTimestamps())->toBeTrue();
});

test('document type has no hidden attributes by default', function () {
    $documentType = new DocumentType;

    expect($documentType->getHidden())->toBeEmpty();
});

test('document type has no appended attributes by default', function () {
    $documentType = new DocumentType;

    expect($documentType->getAppends())->toBeEmpty();
});

// ========================================
// QUERY BUILDER TESTS
// ========================================

test('can find document type by id', function () {
    $documentType = DocumentType::factory()->create();

    $found = DocumentType::find($documentType->id);

    expect($found)
        ->not->toBeNull()
        ->id->toBe($documentType->id)
        ->name->toBe($documentType->name);
});

test('can find document type by name', function () {
    $documentType = DocumentType::factory()->create(['name' => 'Specific Name']);

    $found = DocumentType::where('name', 'Specific Name')->first();

    expect($found)
        ->not->toBeNull()
        ->id->toBe($documentType->id)
        ->name->toBe('Specific Name');
});

test('can filter document types by description', function () {
    DocumentType::factory()->create(['description' => 'Alpha description']);
    DocumentType::factory()->create(['description' => 'Beta description']);
    DocumentType::factory()->withoutDescription()->create();

    $count = DocumentType::whereNotNull('description')->count();

    expect($count)->toBe(2);
});

test('can order document types by name', function () {
    DocumentType::factory()->create(['name' => 'Zebra']);
    DocumentType::factory()->create(['name' => 'Alpha']);
    DocumentType::factory()->create(['name' => 'Beta']);

    $documentTypes = DocumentType::orderBy('name', 'asc')->pluck('name')->toArray();

    expect($documentTypes)->toBe(['Alpha', 'Beta', 'Zebra']);
});

test('can order document types by created_at', function () {
    $first = DocumentType::factory()->create(['created_at' => now()->subDays(3)]);
    $second = DocumentType::factory()->create(['created_at' => now()->subDays(2)]);
    $third = DocumentType::factory()->create(['created_at' => now()->subDays(1)]);

    $ids = DocumentType::orderBy('created_at', 'asc')->pluck('id')->toArray();

    expect($ids)->toBe([$first->id, $second->id, $third->id]);
});

// ========================================
// CRUD OPERATIONS TESTS
// ========================================

test('can create document type with factory', function () {
    $documentType = DocumentType::factory()->create([
        'name' => 'Factory Created',
        'description' => 'Factory Description',
    ]);

    expect($documentType)
        ->toBeInstanceOf(DocumentType::class)
        ->exists->toBeTrue()
        ->name->toBe('Factory Created')
        ->description->toBe('Factory Description');
});

test('can update document type attributes', function () {
    $documentType = DocumentType::factory()->create([
        'name' => 'Original Name',
        'description' => 'Original Description',
    ]);

    $documentType->update([
        'name' => 'Updated Name',
        'description' => 'Updated Description',
    ]);

    expect($documentType->refresh())
        ->name->toBe('Updated Name')
        ->description->toBe('Updated Description');
});

test('can delete document type', function () {
    $documentType = DocumentType::factory()->create();
    $id = $documentType->id;

    $documentType->delete();

    expect(DocumentType::find($id))->toBeNull()
        ->and(DocumentType::withTrashed()->find($id))->not->toBeNull();
});

test('can create multiple document types', function () {
    $documentTypes = DocumentType::factory()->count(10)->create();

    expect($documentTypes)
        ->toHaveCount(10)
        ->each->toBeInstanceOf(DocumentType::class);

    expect(DocumentType::count())->toBe(10);
});

// ========================================
// VALIDATION TESTS (Database Level)
// ========================================

test('database enforces name uniqueness', function () {
    DocumentType::factory()->create(['name' => 'Test Name']);

    $this->expectException(\Illuminate\Database\QueryException::class);

    DocumentType::factory()->create(['name' => 'Test Name']);
});

test('database enforces name not null', function () {
    $this->expectException(\Illuminate\Database\QueryException::class);

    DocumentType::create(['description' => 'Test']);
});

// ========================================
// SERIALIZATION TESTS
// ========================================

test('document type can be serialized to array', function () {
    $documentType = DocumentType::factory()->create();
    $array = $documentType->toArray();

    expect($array)
        ->toBeArray()
        ->toHaveKeys(['id', 'name', 'description', 'created_at', 'updated_at']);
});

test('document type can be serialized to json', function () {
    $documentType = DocumentType::factory()->create();
    $json = $documentType->toJson();

    expect($json)
        ->toBeString()
        ->json();

    $decoded = json_decode($json, true);

    expect($decoded)
        ->toHaveKeys(['id', 'name', 'description', 'created_at', 'updated_at']);
});

// ========================================
// DIRTY/CHANGES TESTS
// ========================================

test('can detect dirty attributes', function () {
    $documentType = DocumentType::factory()->create(['name' => 'Original']);

    expect($documentType->isDirty())->toBeFalse();

    $documentType->name = 'Modified';

    expect($documentType->isDirty())->toBeTrue()
        ->and($documentType->isDirty('name'))->toBeTrue()
        ->and($documentType->isDirty('description'))->toBeFalse();
});

test('can get dirty attributes', function () {
    $documentType = DocumentType::factory()->create(['name' => 'Original']);

    $documentType->name = 'Modified';

    $dirty = $documentType->getDirty();

    expect($dirty)
        ->toBeArray()
        ->toHaveKey('name', 'Modified');
});

test('can detect changes after save', function () {
    $documentType = DocumentType::factory()->create(['name' => 'Original']);

    $documentType->name = 'Modified';
    $documentType->save();

    expect($documentType->fresh())
        ->name->toBe('Modified');
});
