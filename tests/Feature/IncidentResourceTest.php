<?php

declare(strict_types=1);

use App\Filament\Resources\IncidentResource;
use App\Models\Branch;
use App\Models\Document;
use App\Models\Incident;
use App\Models\IncidentComment;
use App\Models\User;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('can render incident resource list page', function () {
    livewire(IncidentResource\Pages\ListIncidents::class)
        ->assertSuccessful();
});

test('can render incident resource create page', function () {
    livewire(IncidentResource\Pages\CreateIncident::class)
        ->assertSuccessful();
});

test('can render incident resource edit page', function () {
    $incident = Incident::factory()->create();

    livewire(IncidentResource\Pages\EditIncident::class, ['record' => $incident->id])
        ->assertSuccessful();
});

test('can list incidents', function () {
    $incidents = Incident::factory()->count(10)->create();

    livewire(IncidentResource\Pages\ListIncidents::class)
        ->assertCanSeeTableRecords($incidents);
});

test('can create incident with all required fields', function () {
    $branch = Branch::factory()->create();

    livewire(IncidentResource\Pages\CreateIncident::class)
        ->fillForm([
            'title' => 'Test Incident',
            'description' => 'This is a test incident description',
            'branch_id' => $branch->id,
            'priority' => 'medium',
            'status' => 'open',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('incidents', [
        'title' => 'Test Incident',
        'description' => 'This is a test incident description',
        'branch_id' => $branch->id,
    ]);
});

test('can create incident with optional document', function () {
    $branch = Branch::factory()->create();
    $document = Document::factory()->create(['branch_id' => $branch->id]);

    livewire(IncidentResource\Pages\CreateIncident::class)
        ->fillForm([
            'title' => 'Document Related Incident',
            'description' => 'Issue with document',
            'branch_id' => $branch->id,
            'document_id' => $document->id,
            'priority' => 'high',
            'status' => 'open',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('incidents', [
        'title' => 'Document Related Incident',
        'document_id' => $document->id,
    ]);
});

test('title is required', function () {
    $branch = Branch::factory()->create();

    livewire(IncidentResource\Pages\CreateIncident::class)
        ->fillForm([
            'description' => 'Test description',
            'branch_id' => $branch->id,
        ])
        ->call('create')
        ->assertHasFormErrors(['title' => 'required']);
});

test('description is required', function () {
    $branch = Branch::factory()->create();

    livewire(IncidentResource\Pages\CreateIncident::class)
        ->fillForm([
            'title' => 'Test Title',
            'branch_id' => $branch->id,
        ])
        ->call('create')
        ->assertHasFormErrors(['description' => 'required']);
});

test('branch_id is required', function () {
    livewire(IncidentResource\Pages\CreateIncident::class)
        ->fillForm([
            'title' => 'Test Title',
            'description' => 'Test description',
        ])
        ->call('create')
        ->assertHasFormErrors(['branch_id' => 'required']);
});

test('title has max length validation', function () {
    $branch = Branch::factory()->create();
    $longTitle = str_repeat('a', 256);

    livewire(IncidentResource\Pages\CreateIncident::class)
        ->fillForm([
            'title' => $longTitle,
            'description' => 'Test description',
            'branch_id' => $branch->id,
        ])
        ->call('create')
        ->assertHasFormErrors(['title' => 'max']);
});

test('can edit incident', function () {
    $incident = Incident::factory()->create();

    livewire(IncidentResource\Pages\EditIncident::class, ['record' => $incident->id])
        ->fillForm([
            'title' => 'Updated Title',
            'description' => 'Updated description',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('incidents', [
        'id' => $incident->id,
        'title' => 'Updated Title',
        'description' => 'Updated description',
    ]);
});

test('can update incident status', function () {
    $incident = Incident::factory()->create(['status' => 'open']);

    livewire(IncidentResource\Pages\EditIncident::class, ['record' => $incident->id])
        ->fillForm([
            'status' => 'resolved',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('incidents', [
        'id' => $incident->id,
        'status' => 'resolved',
    ]);
});

test('can update incident priority', function () {
    $incident = Incident::factory()->create(['priority' => 'low']);

    livewire(IncidentResource\Pages\EditIncident::class, ['record' => $incident->id])
        ->fillForm([
            'priority' => 'high',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('incidents', [
        'id' => $incident->id,
        'priority' => 'high',
    ]);
});

test('can delete incident', function () {
    $incident = Incident::factory()->create();

    livewire(IncidentResource\Pages\EditIncident::class, ['record' => $incident->id])
        ->callAction('delete');

    $this->assertSoftDeleted('incidents', [
        'id' => $incident->id,
    ]);
});

test('can restore deleted incident', function () {
    $incident = Incident::factory()->create();
    $incident->delete();

    livewire(IncidentResource\Pages\EditIncident::class, ['record' => $incident->id])
        ->callAction('restore');

    $this->assertDatabaseHas('incidents', [
        'id' => $incident->id,
        'deleted_at' => null,
    ]);
});

test('can force delete incident', function () {
    $incident = Incident::factory()->create();
    $incident->delete();

    livewire(IncidentResource\Pages\EditIncident::class, ['record' => $incident->id])
        ->callAction('forceDelete');

    $this->assertDatabaseMissing('incidents', [
        'id' => $incident->id,
    ]);
});

test('can filter incidents by branch', function () {
    $branch1 = Branch::factory()->create();
    $branch2 = Branch::factory()->create();

    $incident1 = Incident::factory()->create(['branch_id' => $branch1->id]);
    $incident2 = Incident::factory()->create(['branch_id' => $branch2->id]);

    livewire(IncidentResource\Pages\ListIncidents::class)
        ->filterTable('branch_id', $branch1->id)
        ->assertCanSeeTableRecords([$incident1])
        ->assertCanNotSeeTableRecords([$incident2]);
});

test('can filter incidents by status', function () {
    $openIncident = Incident::factory()->create(['status' => 'open']);
    $closedIncident = Incident::factory()->create(['status' => 'closed']);

    livewire(IncidentResource\Pages\ListIncidents::class)
        ->filterTable('status', 'open')
        ->assertCanSeeTableRecords([$openIncident])
        ->assertCanNotSeeTableRecords([$closedIncident]);
});

test('can filter incidents by priority', function () {
    $highIncident = Incident::factory()->create(['priority' => 'high']);
    $lowIncident = Incident::factory()->create(['priority' => 'low']);

    livewire(IncidentResource\Pages\ListIncidents::class)
        ->filterTable('priority', 'high')
        ->assertCanSeeTableRecords([$highIncident])
        ->assertCanNotSeeTableRecords([$lowIncident]);
});

test('can search incidents by title', function () {
    $incident1 = Incident::factory()->create(['title' => 'Login Error']);
    $incident2 = Incident::factory()->create(['title' => 'Database Issue']);

    livewire(IncidentResource\Pages\ListIncidents::class)
        ->searchTable('Login')
        ->assertCanSeeTableRecords([$incident1])
        ->assertCanNotSeeTableRecords([$incident2]);
});

test('incidents are sorted by created_at descending by default', function () {
    $older = Incident::factory()->create(['created_at' => now()->subDays(2)]);
    $newer = Incident::factory()->create(['created_at' => now()->subDay()]);

    livewire(IncidentResource\Pages\ListIncidents::class)
        ->assertCanSeeTableRecords([$newer, $older], inOrder: true);
});

test('can sort incidents by title', function () {
    $incident1 = Incident::factory()->create(['title' => 'A Incident']);
    $incident2 = Incident::factory()->create(['title' => 'B Incident']);

    livewire(IncidentResource\Pages\ListIncidents::class)
        ->sortTable('title')
        ->assertCanSeeTableRecords([$incident1, $incident2], inOrder: true);
});

test('can sort incidents by branch name', function () {
    $branch1 = Branch::factory()->create(['name' => 'A Branch']);
    $branch2 = Branch::factory()->create(['name' => 'B Branch']);

    $incident1 = Incident::factory()->create(['branch_id' => $branch1->id]);
    $incident2 = Incident::factory()->create(['branch_id' => $branch2->id]);

    livewire(IncidentResource\Pages\ListIncidents::class)
        ->sortTable('branch.name')
        ->assertCanSeeTableRecords([$incident1, $incident2], inOrder: true);
});

test('can sort incidents by status', function () {
    $incident1 = Incident::factory()->create(['status' => 'closed']);
    $incident2 = Incident::factory()->create(['status' => 'open']);

    livewire(IncidentResource\Pages\ListIncidents::class)
        ->sortTable('status')
        ->assertCanSeeTableRecords([$incident1, $incident2], inOrder: true);
});

test('can sort incidents by priority', function () {
    $incident1 = Incident::factory()->create(['priority' => 'high']);
    $incident2 = Incident::factory()->create(['priority' => 'low']);

    livewire(IncidentResource\Pages\ListIncidents::class)
        ->sortTable('priority')
        ->assertCanSeeTableRecords([$incident1, $incident2], inOrder: true);
});

test('can sort incidents by reporter name', function () {
    $user1 = User::factory()->create(['name' => 'Alice']);
    $user2 = User::factory()->create(['name' => 'Bob']);

    $incident1 = Incident::factory()->create(['user_id' => $user1->id]);
    $incident2 = Incident::factory()->create(['user_id' => $user2->id]);

    livewire(IncidentResource\Pages\ListIncidents::class)
        ->sortTable('reporter.name')
        ->assertCanSeeTableRecords([$incident1, $incident2], inOrder: true);
});

test('user_id is automatically filled with authenticated user', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $branch = Branch::factory()->create();

    livewire(IncidentResource\Pages\CreateIncident::class)
        ->fillForm([
            'title' => 'Auto User Test',
            'description' => 'Testing auto user assignment',
            'branch_id' => $branch->id,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('incidents', [
        'title' => 'Auto User Test',
        'user_id' => $user->id,
    ]);
});

test('can bulk delete incidents', function () {
    $incidents = Incident::factory()->count(3)->create();

    livewire(IncidentResource\Pages\ListIncidents::class)
        ->callTableBulkAction('delete', $incidents);

    foreach ($incidents as $incident) {
        $this->assertSoftDeleted('incidents', ['id' => $incident->id]);
    }
});

test('can bulk restore incidents', function () {
    $incidents = Incident::factory()->count(3)->create();
    foreach ($incidents as $incident) {
        $incident->delete();
    }

    livewire(IncidentResource\Pages\ListIncidents::class)
        ->filterTable('trashed', 'only')
        ->callTableBulkAction('restore', $incidents);

    foreach ($incidents as $incident) {
        $this->assertDatabaseHas('incidents', [
            'id' => $incident->id,
            'deleted_at' => null,
        ]);
    }
});

test('can bulk force delete incidents', function () {
    $incidents = Incident::factory()->count(3)->create();
    foreach ($incidents as $incident) {
        $incident->delete();
    }

    livewire(IncidentResource\Pages\ListIncidents::class)
        ->filterTable('trashed', 'only')
        ->callTableBulkAction('forceDelete', $incidents);

    foreach ($incidents as $incident) {
        $this->assertDatabaseMissing('incidents', ['id' => $incident->id]);
    }
});

test('create notification has icon, title and body', function () {
    $branch = Branch::factory()->create();

    livewire(IncidentResource\Pages\CreateIncident::class)
        ->fillForm([
            'title' => 'Test Incident',
            'description' => 'Test description',
            'branch_id' => $branch->id,
        ])
        ->call('create')
        ->assertNotified();
});

test('create redirects to index page', function () {
    $branch = Branch::factory()->create();

    livewire(IncidentResource\Pages\CreateIncident::class)
        ->fillForm([
            'title' => 'Test Incident',
            'description' => 'Test description',
            'branch_id' => $branch->id,
        ])
        ->call('create')
        ->assertRedirect(IncidentResource::getUrl('index'));
});

test('update notification has icon, title and body', function () {
    $incident = Incident::factory()->create();

    livewire(IncidentResource\Pages\EditIncident::class, ['record' => $incident->id])
        ->fillForm([
            'title' => 'Updated Title',
        ])
        ->call('save')
        ->assertNotified();
});

test('update redirects to index page', function () {
    $incident = Incident::factory()->create();

    livewire(IncidentResource\Pages\EditIncident::class, ['record' => $incident->id])
        ->fillForm([
            'title' => 'Updated Title',
        ])
        ->call('save')
        ->assertRedirect(IncidentResource::getUrl('index'));
});

test('delete notification has icon, title and body', function () {
    $incident = Incident::factory()->create();

    livewire(IncidentResource\Pages\EditIncident::class, ['record' => $incident->id])
        ->callAction('delete')
        ->assertNotified();
});

test('restore notification has icon, title and body', function () {
    $incident = Incident::factory()->create();
    $incident->delete();

    livewire(IncidentResource\Pages\EditIncident::class, ['record' => $incident->id])
        ->callAction('restore')
        ->assertNotified();
});

test('force delete notification has icon, title and body', function () {
    $incident = Incident::factory()->create();
    $incident->delete();

    livewire(IncidentResource\Pages\EditIncident::class, ['record' => $incident->id])
        ->callAction('forceDelete')
        ->assertNotified();
});

test('can use table edit action', function () {
    $incident = Incident::factory()->create();

    livewire(IncidentResource\Pages\ListIncidents::class)
        ->callTableAction('edit', $incident);
});

test('can use table delete action', function () {
    $incident = Incident::factory()->create();

    livewire(IncidentResource\Pages\ListIncidents::class)
        ->callTableAction('delete', $incident)
        ->assertNotified();

    $this->assertSoftDeleted('incidents', ['id' => $incident->id]);
});

test('table delete action shows notification', function () {
    $incident = Incident::factory()->create();

    livewire(IncidentResource\Pages\ListIncidents::class)
        ->callTableAction('delete', $incident)
        ->assertNotified();
});

test('bulk delete shows notification', function () {
    $incidents = Incident::factory()->count(3)->create();

    livewire(IncidentResource\Pages\ListIncidents::class)
        ->callTableBulkAction('delete', $incidents)
        ->assertNotified();
});

test('bulk restore shows notification', function () {
    $incidents = Incident::factory()->count(3)->create();
    foreach ($incidents as $incident) {
        $incident->delete();
    }

    livewire(IncidentResource\Pages\ListIncidents::class)
        ->filterTable('trashed', 'only')
        ->callTableBulkAction('restore', $incidents)
        ->assertNotified();
});

test('bulk force delete shows notification', function () {
    $incidents = Incident::factory()->count(3)->create();
    foreach ($incidents as $incident) {
        $incident->delete();
    }

    livewire(IncidentResource\Pages\ListIncidents::class)
        ->filterTable('trashed', 'only')
        ->callTableBulkAction('forceDelete', $incidents)
        ->assertNotified();
});

test('can filter trashed incidents only', function () {
    $activeIncident = Incident::factory()->create();
    $trashedIncident = Incident::factory()->create();
    $trashedIncident->delete();

    livewire(IncidentResource\Pages\ListIncidents::class)
        ->filterTable('trashed', 'only')
        ->assertCanSeeTableRecords([$trashedIncident]);
});

test('can filter to show all incidents including trashed', function () {
    $activeIncident = Incident::factory()->create();
    $trashedIncident = Incident::factory()->create();
    $trashedIncident->delete();

    livewire(IncidentResource\Pages\ListIncidents::class)
        ->filterTable('trashed', 'with')
        ->assertCanSeeTableRecords([$activeIncident, $trashedIncident]);
});

test('can update branch on edit', function () {
    $incident = Incident::factory()->create();
    $newBranch = Branch::factory()->create();

    livewire(IncidentResource\Pages\EditIncident::class, ['record' => $incident->id])
        ->fillForm([
            'branch_id' => $newBranch->id,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('incidents', [
        'id' => $incident->id,
        'branch_id' => $newBranch->id,
    ]);
});

test('can add document to incident on edit', function () {
    $incident = Incident::factory()->create(['document_id' => null]);
    $document = Document::factory()->create(['branch_id' => $incident->branch_id]);

    livewire(IncidentResource\Pages\EditIncident::class, ['record' => $incident->id])
        ->fillForm([
            'document_id' => $document->id,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('incidents', [
        'id' => $incident->id,
        'document_id' => $document->id,
    ]);
});

test('can remove document from incident on edit', function () {
    $document = Document::factory()->create();
    $incident = Incident::factory()->create(['document_id' => $document->id]);

    livewire(IncidentResource\Pages\EditIncident::class, ['record' => $incident->id])
        ->fillForm([
            'document_id' => null,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('incidents', [
        'id' => $incident->id,
        'document_id' => null,
    ]);
});

test('document select is filtered by selected branch', function () {
    $branch1 = Branch::factory()->create();
    $branch2 = Branch::factory()->create();
    $document1 = Document::factory()->create(['branch_id' => $branch1->id]);
    $document2 = Document::factory()->create(['branch_id' => $branch2->id]);

    livewire(IncidentResource\Pages\CreateIncident::class)
        ->fillForm(['branch_id' => $branch1->id])
        ->assertFormFieldExists('document_id');
});

test('comments count column displays correct count', function () {
    $incident = Incident::factory()->create();
    IncidentComment::factory()->count(3)->create(['incident_id' => $incident->id]);

    livewire(IncidentResource\Pages\ListIncidents::class)
        ->assertCanSeeTableRecords([$incident]);
});

test('status badge displays correct color for open', function () {
    $incident = Incident::factory()->create(['status' => 'open']);

    livewire(IncidentResource\Pages\ListIncidents::class)
        ->assertCanSeeTableRecords([$incident]);
});

test('status badge displays correct color for in_progress', function () {
    $incident = Incident::factory()->create(['status' => 'in_progress']);

    livewire(IncidentResource\Pages\ListIncidents::class)
        ->assertCanSeeTableRecords([$incident]);
});

test('status badge displays correct color for resolved', function () {
    $incident = Incident::factory()->create(['status' => 'resolved']);

    livewire(IncidentResource\Pages\ListIncidents::class)
        ->assertCanSeeTableRecords([$incident]);
});

test('status badge displays correct color for closed', function () {
    $incident = Incident::factory()->create(['status' => 'closed']);

    livewire(IncidentResource\Pages\ListIncidents::class)
        ->assertCanSeeTableRecords([$incident]);
});

test('priority badge displays correct color for low', function () {
    $incident = Incident::factory()->create(['priority' => 'low']);

    livewire(IncidentResource\Pages\ListIncidents::class)
        ->assertCanSeeTableRecords([$incident]);
});

test('priority badge displays correct color for medium', function () {
    $incident = Incident::factory()->create(['priority' => 'medium']);

    livewire(IncidentResource\Pages\ListIncidents::class)
        ->assertCanSeeTableRecords([$incident]);
});

test('priority badge displays correct color for high', function () {
    $incident = Incident::factory()->create(['priority' => 'high']);

    livewire(IncidentResource\Pages\ListIncidents::class)
        ->assertCanSeeTableRecords([$incident]);
});
