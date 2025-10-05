<?php

declare(strict_types=1);

use App\Filament\Resources\DocumentResource;
use App\Models\Branch;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('can render document resource list page', function () {
    livewire(DocumentResource\Pages\ListDocuments::class)
        ->assertSuccessful();
});

test('can render document resource create page', function () {
    livewire(DocumentResource\Pages\CreateDocument::class)
        ->assertSuccessful();
});

test('can render document resource edit page', function () {
    $document = Document::factory()->create();

    livewire(DocumentResource\Pages\EditDocument::class, ['record' => $document->id])
        ->assertSuccessful();
});

test('can list documents', function () {
    $documents = Document::factory()->count(10)->create();

    livewire(DocumentResource\Pages\ListDocuments::class)
        ->assertCanSeeTableRecords($documents);
});

test('can create document with all required fields', function () {
    Storage::fake('private');

    $documentType = DocumentType::factory()->create();
    $branch = Branch::factory()->create();
    $file = UploadedFile::fake()->create('document.pdf', 1024);

    livewire(DocumentResource\Pages\CreateDocument::class)
        ->fillForm([
            'document_type_id' => $documentType->id,
            'branch_id' => $branch->id,
            'file_path' => $file,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('documents', [
        'document_type_id' => $documentType->id,
        'branch_id' => $branch->id,
    ]);
});

test('can create document with optional description', function () {
    Storage::fake('private');

    $documentType = DocumentType::factory()->create();
    $branch = Branch::factory()->create();
    $file = UploadedFile::fake()->create('document.pdf', 1024);

    livewire(DocumentResource\Pages\CreateDocument::class)
        ->fillForm([
            'document_type_id' => $documentType->id,
            'branch_id' => $branch->id,
            'description' => 'Test document description',
            'file_path' => $file,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('documents', [
        'description' => 'Test document description',
    ]);
});

test('can create document with optional expires_at', function () {
    Storage::fake('private');

    $documentType = DocumentType::factory()->create();
    $branch = Branch::factory()->create();
    $file = UploadedFile::fake()->create('document.pdf', 1024);
    $expiresAt = now()->addDays(30);

    livewire(DocumentResource\Pages\CreateDocument::class)
        ->fillForm([
            'document_type_id' => $documentType->id,
            'branch_id' => $branch->id,
            'expires_at' => $expiresAt,
            'file_path' => $file,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $document = Document::latest()->first();
    expect($document->expires_at->format('Y-m-d'))->toBe($expiresAt->format('Y-m-d'));
});

test('document_type_id is required', function () {
    Storage::fake('private');

    $branch = Branch::factory()->create();
    $file = UploadedFile::fake()->create('document.pdf', 1024);

    livewire(DocumentResource\Pages\CreateDocument::class)
        ->fillForm([
            'branch_id' => $branch->id,
            'file_path' => $file,
        ])
        ->call('create')
        ->assertHasFormErrors(['document_type_id' => 'required']);
});

test('branch_id is required', function () {
    Storage::fake('private');

    $documentType = DocumentType::factory()->create();
    $file = UploadedFile::fake()->create('document.pdf', 1024);

    livewire(DocumentResource\Pages\CreateDocument::class)
        ->fillForm([
            'document_type_id' => $documentType->id,
            'file_path' => $file,
        ])
        ->call('create')
        ->assertHasFormErrors(['branch_id' => 'required']);
});

test('file is required', function () {
    $documentType = DocumentType::factory()->create();
    $branch = Branch::factory()->create();

    livewire(DocumentResource\Pages\CreateDocument::class)
        ->fillForm([
            'document_type_id' => $documentType->id,
            'branch_id' => $branch->id,
        ])
        ->call('create')
        ->assertHasFormErrors(['file_path' => 'required']);
});

test('can edit document', function () {
    Storage::fake('private');

    $document = Document::factory()->create();

    livewire(DocumentResource\Pages\EditDocument::class, ['record' => $document->id])
        ->fillForm([
            'description' => 'Updated description',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('documents', [
        'id' => $document->id,
        'description' => 'Updated description',
    ]);
});

test('can delete document', function () {
    $document = Document::factory()->create();

    livewire(DocumentResource\Pages\EditDocument::class, ['record' => $document->id])
        ->callAction('delete');

    $this->assertSoftDeleted('documents', [
        'id' => $document->id,
    ]);
});

test('can restore deleted document', function () {
    $document = Document::factory()->create();
    $document->delete();

    livewire(DocumentResource\Pages\EditDocument::class, ['record' => $document->id])
        ->callAction('restore');

    $this->assertDatabaseHas('documents', [
        'id' => $document->id,
        'deleted_at' => null,
    ]);
});

test('can force delete document', function () {
    $document = Document::factory()->create();
    $document->delete();

    livewire(DocumentResource\Pages\EditDocument::class, ['record' => $document->id])
        ->callAction('forceDelete');

    $this->assertDatabaseMissing('documents', [
        'id' => $document->id,
    ]);
});

test('can filter documents by document type', function () {
    $documentType1 = DocumentType::factory()->create();
    $documentType2 = DocumentType::factory()->create();

    $document1 = Document::factory()->create(['document_type_id' => $documentType1->id]);
    $document2 = Document::factory()->create(['document_type_id' => $documentType2->id]);

    livewire(DocumentResource\Pages\ListDocuments::class)
        ->filterTable('document_type_id', $documentType1->id)
        ->assertCanSeeTableRecords([$document1])
        ->assertCanNotSeeTableRecords([$document2]);
});

test('can filter documents by branch', function () {
    $branch1 = Branch::factory()->create();
    $branch2 = Branch::factory()->create();

    $document1 = Document::factory()->create(['branch_id' => $branch1->id]);
    $document2 = Document::factory()->create(['branch_id' => $branch2->id]);

    livewire(DocumentResource\Pages\ListDocuments::class)
        ->filterTable('branch_id', $branch1->id)
        ->assertCanSeeTableRecords([$document1])
        ->assertCanNotSeeTableRecords([$document2]);
});

test('can filter documents by uploaded_by', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $document1 = Document::factory()->create(['uploaded_by' => $user1->id]);
    $document2 = Document::factory()->create(['uploaded_by' => $user2->id]);

    livewire(DocumentResource\Pages\ListDocuments::class)
        ->filterTable('uploaded_by', $user1->id)
        ->assertCanSeeTableRecords([$document1])
        ->assertCanNotSeeTableRecords([$document2]);
});

test('can filter expired documents', function () {
    $expiredDocument = Document::factory()->expired()->create();
    $activeDocument = Document::factory()->create(['expires_at' => now()->addDays(30)]);

    livewire(DocumentResource\Pages\ListDocuments::class)
        ->filterTable('expired', true)
        ->assertCanSeeTableRecords([$expiredDocument])
        ->assertCanNotSeeTableRecords([$activeDocument]);
});

test('can filter active documents only', function () {
    $expiredDocument = Document::factory()->expired()->create();
    $activeDocument = Document::factory()->create(['expires_at' => now()->addDays(30)]);

    livewire(DocumentResource\Pages\ListDocuments::class)
        ->filterTable('expired', false)
        ->assertCanSeeTableRecords([$activeDocument])
        ->assertCanNotSeeTableRecords([$expiredDocument]);
});

test('can search documents by description', function () {
    $document1 = Document::factory()->create(['description' => 'Important contract document']);
    $document2 = Document::factory()->create(['description' => 'Employee handbook']);

    livewire(DocumentResource\Pages\ListDocuments::class)
        ->searchTable('contract')
        ->assertCanSeeTableRecords([$document1])
        ->assertCanNotSeeTableRecords([$document2]);
});

test('documents are sorted by created_at descending by default', function () {
    $older = Document::factory()->create(['created_at' => now()->subDays(2)]);
    $newer = Document::factory()->create(['created_at' => now()->subDay()]);

    livewire(DocumentResource\Pages\ListDocuments::class)
        ->assertCanSeeTableRecords([$newer, $older], inOrder: true);
});

test('can sort documents by document type', function () {
    $documentType1 = DocumentType::factory()->create(['name' => 'A Type']);
    $documentType2 = DocumentType::factory()->create(['name' => 'B Type']);

    $document1 = Document::factory()->create(['document_type_id' => $documentType1->id]);
    $document2 = Document::factory()->create(['document_type_id' => $documentType2->id]);

    livewire(DocumentResource\Pages\ListDocuments::class)
        ->sortTable('documentType.name')
        ->assertCanSeeTableRecords([$document1, $document2], inOrder: true);
});

test('uploaded_by is automatically filled with authenticated user', function () {
    Storage::fake('private');

    $user = User::factory()->create();
    $this->actingAs($user);

    $documentType = DocumentType::factory()->create();
    $branch = Branch::factory()->create();
    $file = UploadedFile::fake()->create('document.pdf', 1024);

    livewire(DocumentResource\Pages\CreateDocument::class)
        ->fillForm([
            'document_type_id' => $documentType->id,
            'branch_id' => $branch->id,
            'file_path' => $file,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('documents', [
        'uploaded_by' => $user->id,
    ]);
});

test('can bulk delete documents', function () {
    $documents = Document::factory()->count(3)->create();

    livewire(DocumentResource\Pages\ListDocuments::class)
        ->callTableBulkAction('delete', $documents);

    foreach ($documents as $document) {
        $this->assertSoftDeleted('documents', ['id' => $document->id]);
    }
});

test('can bulk restore documents', function () {
    $documents = Document::factory()->count(3)->create();
    foreach ($documents as $document) {
        $document->delete();
    }

    livewire(DocumentResource\Pages\ListDocuments::class)
        ->filterTable('trashed', 'only')
        ->callTableBulkAction('restore', $documents);

    foreach ($documents as $document) {
        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'deleted_at' => null,
        ]);
    }
});

test('can bulk force delete documents', function () {
    $documents = Document::factory()->count(3)->create();
    foreach ($documents as $document) {
        $document->delete();
    }

    livewire(DocumentResource\Pages\ListDocuments::class)
        ->filterTable('trashed', 'only')
        ->callTableBulkAction('forceDelete', $documents);

    foreach ($documents as $document) {
        $this->assertDatabaseMissing('documents', ['id' => $document->id]);
    }
});

test('metadata is auto-captured on file upload', function () {
    Storage::fake('private');

    $documentType = DocumentType::factory()->create();
    $branch = Branch::factory()->create();
    $file = UploadedFile::fake()->create('test-document.pdf', 1024, 'application/pdf');

    livewire(DocumentResource\Pages\CreateDocument::class)
        ->fillForm([
            'document_type_id' => $documentType->id,
            'branch_id' => $branch->id,
            'file_path' => $file,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $document = Document::latest()->first();

    expect($document->original_filename)->toBeString()
        ->and($document->file_size)->toBeInt()
        ->and($document->mime_type)->toBeString();
});
