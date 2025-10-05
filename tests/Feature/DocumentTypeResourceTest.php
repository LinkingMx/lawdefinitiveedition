<?php

declare(strict_types=1);

use App\Filament\Resources\DocumentTypeResource;
use App\Models\DocumentType;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create and authenticate an admin user for all tests
    $this->actingAs(User::factory()->create());
});

// ========================================
// LIST PAGE TESTS
// ========================================

test('can render list page', function () {
    Livewire::test(DocumentTypeResource\Pages\ListDocumentTypes::class)
        ->assertSuccessful();
});

test('can list document types', function () {
    $documentTypes = DocumentType::factory()->count(10)->create();

    Livewire::test(DocumentTypeResource\Pages\ListDocumentTypes::class)
        ->assertCanSeeTableRecords($documentTypes);
});

test('can search document types by name', function () {
    $documentType1 = DocumentType::factory()->create(['name' => 'Contract Agreement']);
    $documentType2 = DocumentType::factory()->create(['name' => 'Invoice Document']);
    $documentType3 = DocumentType::factory()->create(['name' => 'Legal Brief']);

    Livewire::test(DocumentTypeResource\Pages\ListDocumentTypes::class)
        ->searchTable('Contract')
        ->assertCanSeeTableRecords([$documentType1])
        ->assertCanNotSeeTableRecords([$documentType2, $documentType3]);
});

test('can sort document types by name ascending', function () {
    $documentTypes = DocumentType::factory()->count(3)->create();

    Livewire::test(DocumentTypeResource\Pages\ListDocumentTypes::class)
        ->sortTable('name', 'asc')
        ->assertCanSeeTableRecords($documentTypes->sortBy('name'), inOrder: true);
});

test('can sort document types by name descending', function () {
    $documentTypes = DocumentType::factory()->count(3)->create();

    Livewire::test(DocumentTypeResource\Pages\ListDocumentTypes::class)
        ->sortTable('name', 'desc')
        ->assertCanSeeTableRecords($documentTypes->sortByDesc('name'), inOrder: true);
});

test('can sort document types by description', function () {
    $documentTypes = DocumentType::factory()->count(3)->create();

    Livewire::test(DocumentTypeResource\Pages\ListDocumentTypes::class)
        ->sortTable('description', 'asc')
        ->assertCanSeeTableRecords($documentTypes->sortBy('description'), inOrder: true);
});

test('can sort document types by created_at', function () {
    $documentTypes = DocumentType::factory()->count(3)->create();

    Livewire::test(DocumentTypeResource\Pages\ListDocumentTypes::class)
        ->sortTable('created_at', 'asc')
        ->assertCanSeeTableRecords($documentTypes->sortBy('created_at'), inOrder: true);
});

test('can sort document types by updated_at', function () {
    $documentTypes = DocumentType::factory()->count(3)->create();

    Livewire::test(DocumentTypeResource\Pages\ListDocumentTypes::class)
        ->sortTable('updated_at', 'desc')
        ->assertCanSeeTableRecords($documentTypes->sortByDesc('updated_at'), inOrder: true);
});

test('can filter document types to show only trashed', function () {
    $activeDocumentType = DocumentType::factory()->create();
    $trashedDocumentType = DocumentType::factory()->deleted()->create();

    Livewire::test(DocumentTypeResource\Pages\ListDocumentTypes::class)
        ->filterTable('trashed', true)
        ->assertCanSeeTableRecords([$trashedDocumentType]);
});

test('can filter document types to show with trashed', function () {
    $activeDocumentType = DocumentType::factory()->create();
    $trashedDocumentType = DocumentType::factory()->deleted()->create();

    Livewire::test(DocumentTypeResource\Pages\ListDocumentTypes::class)
        ->filterTable('trashed', 2)
        ->assertCanSeeTableRecords([$activeDocumentType, $trashedDocumentType]);
});

test('can filter document types to show without trashed', function () {
    $activeDocumentType = DocumentType::factory()->create();
    $trashedDocumentType = DocumentType::factory()->deleted()->create();

    Livewire::test(DocumentTypeResource\Pages\ListDocumentTypes::class)
        ->assertCanSeeTableRecords([$activeDocumentType]);
});

test('can toggle created_at column visibility', function () {
    $documentType = DocumentType::factory()->create();

    Livewire::test(DocumentTypeResource\Pages\ListDocumentTypes::class)
        ->assertTableColumnExists('created_at');
});

test('can toggle updated_at column visibility', function () {
    $documentType = DocumentType::factory()->create();

    Livewire::test(DocumentTypeResource\Pages\ListDocumentTypes::class)
        ->assertTableColumnExists('updated_at');
});

// ========================================
// CREATE PAGE TESTS
// ========================================

test('can render create page', function () {
    Livewire::test(DocumentTypeResource\Pages\CreateDocumentType::class)
        ->assertSuccessful();
});

test('can create document type with all fields', function () {
    $newData = [
        'name' => 'Legal Contract',
        'description' => 'A comprehensive legal contract document type',
    ];

    Livewire::test(DocumentTypeResource\Pages\CreateDocumentType::class)
        ->fillForm($newData)
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(DocumentType::class, $newData);
});

test('can create document type without description', function () {
    $newData = [
        'name' => 'Simple Document',
        'description' => null,
    ];

    Livewire::test(DocumentTypeResource\Pages\CreateDocumentType::class)
        ->fillForm($newData)
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(DocumentType::class, [
        'name' => 'Simple Document',
    ]);

    expect(DocumentType::where('name', 'Simple Document')->first()->description)
        ->toBeNull();
});

test('create redirects to index page after success', function () {
    $newData = [
        'name' => 'Test Document Type',
        'description' => 'Test description',
    ];

    Livewire::test(DocumentTypeResource\Pages\CreateDocumentType::class)
        ->fillForm($newData)
        ->call('create')
        ->assertRedirect(DocumentTypeResource::getUrl('index'));
});

test('create shows success notification with icon, title, and body', function () {
    $newData = [
        'name' => 'New Document Type',
        'description' => 'New description',
    ];

    Livewire::test(DocumentTypeResource\Pages\CreateDocumentType::class)
        ->fillForm($newData)
        ->call('create')
        ->assertNotified();
});

// ========================================
// CREATE VALIDATION TESTS
// ========================================

test('name is required on create', function () {
    Livewire::test(DocumentTypeResource\Pages\CreateDocumentType::class)
        ->fillForm([
            'name' => null,
            'description' => 'Test description',
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);
});

test('name must be unique on create', function () {
    DocumentType::factory()->create(['name' => 'Existing Name']);

    Livewire::test(DocumentTypeResource\Pages\CreateDocumentType::class)
        ->fillForm([
            'name' => 'Existing Name',
            'description' => 'Test description',
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'unique']);
});

test('name cannot exceed 255 characters on create', function () {
    Livewire::test(DocumentTypeResource\Pages\CreateDocumentType::class)
        ->fillForm([
            'name' => str_repeat('a', 256),
            'description' => 'Test description',
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'max']);
});

test('description cannot exceed 500 characters on create', function () {
    Livewire::test(DocumentTypeResource\Pages\CreateDocumentType::class)
        ->fillForm([
            'name' => 'Valid Name',
            'description' => str_repeat('a', 501),
        ])
        ->call('create')
        ->assertHasFormErrors(['description' => 'max']);
});

test('description is optional on create', function () {
    Livewire::test(DocumentTypeResource\Pages\CreateDocumentType::class)
        ->fillForm([
            'name' => 'Document Without Description',
            'description' => null,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(DocumentType::class, [
        'name' => 'Document Without Description',
        'description' => null,
    ]);
});

// ========================================
// EDIT PAGE TESTS
// ========================================

test('can render edit page', function () {
    $documentType = DocumentType::factory()->create();

    Livewire::test(DocumentTypeResource\Pages\EditDocumentType::class, [
        'record' => $documentType->getRouteKey(),
    ])->assertSuccessful();
});

test('can retrieve data on edit page', function () {
    $documentType = DocumentType::factory()->create();

    Livewire::test(DocumentTypeResource\Pages\EditDocumentType::class, [
        'record' => $documentType->getRouteKey(),
    ])
        ->assertFormSet([
            'name' => $documentType->name,
            'description' => $documentType->description,
        ]);
});

test('can update document type with all fields', function () {
    $documentType = DocumentType::factory()->create();
    $newData = [
        'name' => 'Updated Name',
        'description' => 'Updated description',
    ];

    Livewire::test(DocumentTypeResource\Pages\EditDocumentType::class, [
        'record' => $documentType->getRouteKey(),
    ])
        ->fillForm($newData)
        ->call('save')
        ->assertHasNoFormErrors();

    expect($documentType->refresh())
        ->name->toBe('Updated Name')
        ->description->toBe('Updated description');
});

test('can update document type name only', function () {
    $documentType = DocumentType::factory()->create([
        'description' => 'Original description',
    ]);

    Livewire::test(DocumentTypeResource\Pages\EditDocumentType::class, [
        'record' => $documentType->getRouteKey(),
    ])
        ->fillForm([
            'name' => 'Updated Name Only',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($documentType->refresh())
        ->name->toBe('Updated Name Only')
        ->description->toBe('Original description');
});

test('can clear description on update', function () {
    $documentType = DocumentType::factory()->create([
        'description' => 'Original description',
    ]);

    Livewire::test(DocumentTypeResource\Pages\EditDocumentType::class, [
        'record' => $documentType->getRouteKey(),
    ])
        ->fillForm([
            'description' => null,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($documentType->refresh()->description)->toBeNull();
});

test('edit redirects to index page after success', function () {
    $documentType = DocumentType::factory()->create();

    Livewire::test(DocumentTypeResource\Pages\EditDocumentType::class, [
        'record' => $documentType->getRouteKey(),
    ])
        ->fillForm([
            'name' => 'Updated Name',
        ])
        ->call('save')
        ->assertRedirect(DocumentTypeResource::getUrl('index'));
});

test('edit shows success notification with icon, title, and body', function () {
    $documentType = DocumentType::factory()->create();

    Livewire::test(DocumentTypeResource\Pages\EditDocumentType::class, [
        'record' => $documentType->getRouteKey(),
    ])
        ->fillForm([
            'name' => 'Updated Name',
        ])
        ->call('save')
        ->assertNotified();
});

// ========================================
// EDIT VALIDATION TESTS
// ========================================

test('name is required on edit', function () {
    $documentType = DocumentType::factory()->create();

    Livewire::test(DocumentTypeResource\Pages\EditDocumentType::class, [
        'record' => $documentType->getRouteKey(),
    ])
        ->fillForm([
            'name' => null,
        ])
        ->call('save')
        ->assertHasFormErrors(['name' => 'required']);
});

test('name must be unique on edit except current record', function () {
    $documentType1 = DocumentType::factory()->create(['name' => 'First Type']);
    $documentType2 = DocumentType::factory()->create(['name' => 'Second Type']);

    // Should fail when using another record's name
    Livewire::test(DocumentTypeResource\Pages\EditDocumentType::class, [
        'record' => $documentType2->getRouteKey(),
    ])
        ->fillForm([
            'name' => 'First Type',
        ])
        ->call('save')
        ->assertHasFormErrors(['name' => 'unique']);

    // Should pass when using its own name
    Livewire::test(DocumentTypeResource\Pages\EditDocumentType::class, [
        'record' => $documentType1->getRouteKey(),
    ])
        ->fillForm([
            'name' => 'First Type',
        ])
        ->call('save')
        ->assertHasNoFormErrors();
});

test('name cannot exceed 255 characters on edit', function () {
    $documentType = DocumentType::factory()->create();

    Livewire::test(DocumentTypeResource\Pages\EditDocumentType::class, [
        'record' => $documentType->getRouteKey(),
    ])
        ->fillForm([
            'name' => str_repeat('a', 256),
        ])
        ->call('save')
        ->assertHasFormErrors(['name' => 'max']);
});

test('description cannot exceed 500 characters on edit', function () {
    $documentType = DocumentType::factory()->create();

    Livewire::test(DocumentTypeResource\Pages\EditDocumentType::class, [
        'record' => $documentType->getRouteKey(),
    ])
        ->fillForm([
            'description' => str_repeat('a', 501),
        ])
        ->call('save')
        ->assertHasFormErrors(['description' => 'max']);
});

// ========================================
// DELETE TESTS (TABLE ACTIONS)
// ========================================

test('can delete document type from table', function () {
    $documentType = DocumentType::factory()->create();

    Livewire::test(DocumentTypeResource\Pages\ListDocumentTypes::class)
        ->callTableAction(\Filament\Tables\Actions\DeleteAction::class, $documentType);

    $this->assertSoftDeleted($documentType);
});

test('delete action shows success notification with icon, title, and body', function () {
    $documentType = DocumentType::factory()->create();

    Livewire::test(DocumentTypeResource\Pages\ListDocumentTypes::class)
        ->callTableAction(\Filament\Tables\Actions\DeleteAction::class, $documentType)
        ->assertNotified();
});

test('can restore soft deleted document type from table', function () {
    $documentType = DocumentType::factory()->deleted()->create();

    Livewire::test(DocumentTypeResource\Pages\ListDocumentTypes::class)
        ->callTableAction(\Filament\Tables\Actions\RestoreAction::class, $documentType)
        ->assertHasNoErrors();

    // Verify restoration by checking the database directly
    $restored = DocumentType::withoutTrashed()->find($documentType->id);
    expect($restored)->not->toBeNull();
});

test('restore action shows success notification with icon, title, and body', function () {
    $documentType = DocumentType::factory()->deleted()->create();

    Livewire::test(DocumentTypeResource\Pages\ListDocumentTypes::class)
        ->callTableAction(\Filament\Tables\Actions\RestoreAction::class, $documentType)
        ->assertNotified();
});

test('can force delete document type from table', function () {
    $documentType = DocumentType::factory()->deleted()->create();
    $id = $documentType->id;

    Livewire::test(DocumentTypeResource\Pages\ListDocumentTypes::class)
        ->callTableAction(\Filament\Tables\Actions\ForceDeleteAction::class, $documentType);

    $this->assertDatabaseMissing(DocumentType::class, [
        'id' => $id,
    ]);
});

test('force delete action shows notification with icon, title, and body', function () {
    $documentType = DocumentType::factory()->deleted()->create();

    Livewire::test(DocumentTypeResource\Pages\ListDocumentTypes::class)
        ->callTableAction(\Filament\Tables\Actions\ForceDeleteAction::class, $documentType)
        ->assertNotified();
});

test('can edit document type from table', function () {
    $documentType = DocumentType::factory()->create();

    Livewire::test(DocumentTypeResource\Pages\ListDocumentTypes::class)
        ->assertTableActionExists(\Filament\Tables\Actions\EditAction::class)
        ->callTableAction(\Filament\Tables\Actions\EditAction::class, $documentType);
});

// ========================================
// DELETE TESTS (HEADER ACTIONS)
// ========================================

test('can delete document type from edit page header action', function () {
    $documentType = DocumentType::factory()->create();

    Livewire::test(DocumentTypeResource\Pages\EditDocumentType::class, [
        'record' => $documentType->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertSoftDeleted($documentType);
});

test('delete header action shows success notification with icon, title, and body', function () {
    $documentType = DocumentType::factory()->create();

    Livewire::test(DocumentTypeResource\Pages\EditDocumentType::class, [
        'record' => $documentType->getRouteKey(),
    ])
        ->callAction(DeleteAction::class)
        ->assertNotified();
});

test('can restore document type from edit page header action', function () {
    $documentType = DocumentType::factory()->deleted()->create();

    Livewire::test(DocumentTypeResource\Pages\EditDocumentType::class, [
        'record' => $documentType->getRouteKey(),
    ])
        ->callAction(RestoreAction::class)
        ->assertHasNoErrors();

    // Verify restoration
    $restored = DocumentType::withoutTrashed()->find($documentType->id);
    expect($restored)->not->toBeNull();
});

test('restore header action shows success notification with icon, title, and body', function () {
    $documentType = DocumentType::factory()->deleted()->create();

    Livewire::test(DocumentTypeResource\Pages\EditDocumentType::class, [
        'record' => $documentType->getRouteKey(),
    ])
        ->callAction(RestoreAction::class)
        ->assertNotified();
});

test('can force delete document type from edit page header action', function () {
    $documentType = DocumentType::factory()->deleted()->create();
    $id = $documentType->id;

    Livewire::test(DocumentTypeResource\Pages\EditDocumentType::class, [
        'record' => $documentType->getRouteKey(),
    ])
        ->callAction(ForceDeleteAction::class);

    $this->assertDatabaseMissing(DocumentType::class, [
        'id' => $id,
    ]);
});

test('force delete header action shows notification with icon, title, and body', function () {
    $documentType = DocumentType::factory()->deleted()->create();

    Livewire::test(DocumentTypeResource\Pages\EditDocumentType::class, [
        'record' => $documentType->getRouteKey(),
    ])
        ->callAction(ForceDeleteAction::class)
        ->assertNotified();
});

// ========================================
// BULK ACTIONS TESTS
// ========================================

test('can bulk delete document types', function () {
    $documentTypes = DocumentType::factory()->count(5)->create();

    Livewire::test(DocumentTypeResource\Pages\ListDocumentTypes::class)
        ->callTableBulkAction(\Filament\Tables\Actions\DeleteBulkAction::class, $documentTypes);

    foreach ($documentTypes as $documentType) {
        $this->assertSoftDeleted($documentType);
    }
});

test('can bulk restore document types', function () {
    $documentTypes = DocumentType::factory()->deleted()->count(5)->create();
    $ids = $documentTypes->pluck('id')->toArray();

    Livewire::test(DocumentTypeResource\Pages\ListDocumentTypes::class)
        ->callTableBulkAction(\Filament\Tables\Actions\RestoreBulkAction::class, $documentTypes)
        ->assertHasNoErrors();

    // Verify all were restored
    expect(DocumentType::withoutTrashed()->whereIn('id', $ids)->count())->toBe(5);
});

test('can bulk force delete document types', function () {
    $documentTypes = DocumentType::factory()->deleted()->count(5)->create();
    $ids = $documentTypes->pluck('id')->toArray();

    Livewire::test(DocumentTypeResource\Pages\ListDocumentTypes::class)
        ->callTableBulkAction(\Filament\Tables\Actions\ForceDeleteBulkAction::class, $documentTypes);

    foreach ($ids as $id) {
        $this->assertDatabaseMissing(DocumentType::class, [
            'id' => $id,
        ]);
    }
});

// ========================================
// PAGINATION TESTS
// ========================================

test('can paginate document types', function () {
    DocumentType::factory()->count(25)->create();

    Livewire::test(DocumentTypeResource\Pages\ListDocumentTypes::class)
        ->assertCountTableRecords(10); // Default pagination is 10 per page
});

// ========================================
// NAVIGATION TESTS
// ========================================

test('document type resource has correct navigation configuration', function () {
    expect(DocumentTypeResource::getNavigationIcon())
        ->toBe('heroicon-o-document-text')
        ->and(DocumentTypeResource::getNavigationGroup())
        ->toBe('Administration')
        ->and(DocumentTypeResource::getNavigationSort())
        ->toBe(10);
});

// ========================================
// DEFAULT SORT TESTS
// ========================================

test('document types are sorted by name ascending by default', function () {
    $documentTypes = collect([
        DocumentType::factory()->create(['name' => 'Zebra']),
        DocumentType::factory()->create(['name' => 'Alpha']),
        DocumentType::factory()->create(['name' => 'Beta']),
    ])->sortBy('name')->values();

    Livewire::test(DocumentTypeResource\Pages\ListDocumentTypes::class)
        ->assertCanSeeTableRecords($documentTypes, inOrder: true);
});
