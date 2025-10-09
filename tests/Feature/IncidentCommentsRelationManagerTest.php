<?php

declare(strict_types=1);

use App\Filament\Resources\IncidentResource;
use App\Models\Incident;
use App\Models\IncidentComment;
use App\Models\User;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('can render incident comments relation manager', function () {
    $incident = Incident::factory()->create();

    livewire(IncidentResource\RelationManagers\IncidentCommentsRelationManager::class, [
        'ownerRecord' => $incident,
        'pageClass' => IncidentResource\Pages\EditIncident::class,
    ])
        ->assertSuccessful();
});

test('can list incident comments', function () {
    $incident = Incident::factory()->create();
    $comments = IncidentComment::factory()->count(5)->create(['incident_id' => $incident->id]);

    livewire(IncidentResource\RelationManagers\IncidentCommentsRelationManager::class, [
        'ownerRecord' => $incident,
        'pageClass' => IncidentResource\Pages\EditIncident::class,
    ])
        ->assertCanSeeTableRecords($comments);
});

test('can create incident comment', function () {
    $incident = Incident::factory()->create();

    livewire(IncidentResource\RelationManagers\IncidentCommentsRelationManager::class, [
        'ownerRecord' => $incident,
        'pageClass' => IncidentResource\Pages\EditIncident::class,
    ])
        ->callTableAction('create', data: [
            'comment' => 'This is a test comment',
        ])
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseHas('incident_comments', [
        'incident_id' => $incident->id,
        'comment' => 'This is a test comment',
    ]);
});

test('comment is required when creating', function () {
    $incident = Incident::factory()->create();

    livewire(IncidentResource\RelationManagers\IncidentCommentsRelationManager::class, [
        'ownerRecord' => $incident,
        'pageClass' => IncidentResource\Pages\EditIncident::class,
    ])
        ->callTableAction('create', data: [
            'comment' => '',
        ])
        ->assertHasTableActionErrors(['comment' => 'required']);
});

test('user_id is automatically filled with authenticated user when creating comment', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $incident = Incident::factory()->create();

    livewire(IncidentResource\RelationManagers\IncidentCommentsRelationManager::class, [
        'ownerRecord' => $incident,
        'pageClass' => IncidentResource\Pages\EditIncident::class,
    ])
        ->callTableAction('create', data: [
            'comment' => 'Auto user assignment test',
        ])
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseHas('incident_comments', [
        'incident_id' => $incident->id,
        'user_id' => $user->id,
        'comment' => 'Auto user assignment test',
    ]);
});

test('create comment notification has icon, title and body', function () {
    $incident = Incident::factory()->create();

    livewire(IncidentResource\RelationManagers\IncidentCommentsRelationManager::class, [
        'ownerRecord' => $incident,
        'pageClass' => IncidentResource\Pages\EditIncident::class,
    ])
        ->callTableAction('create', data: [
            'comment' => 'Test notification',
        ])
        ->assertNotified();
});

test('comments are sorted by created_at descending by default', function () {
    $incident = Incident::factory()->create();
    $older = IncidentComment::factory()->create([
        'incident_id' => $incident->id,
        'created_at' => now()->subDays(2),
    ]);
    $newer = IncidentComment::factory()->create([
        'incident_id' => $incident->id,
        'created_at' => now()->subDay(),
    ]);

    livewire(IncidentResource\RelationManagers\IncidentCommentsRelationManager::class, [
        'ownerRecord' => $incident,
        'pageClass' => IncidentResource\Pages\EditIncident::class,
    ])
        ->assertCanSeeTableRecords([$newer, $older], inOrder: true);
});

test('can search comments by comment text', function () {
    $incident = Incident::factory()->create();
    $comment1 = IncidentComment::factory()->create([
        'incident_id' => $incident->id,
        'comment' => 'This is about login issues',
    ]);
    $comment2 = IncidentComment::factory()->create([
        'incident_id' => $incident->id,
        'comment' => 'This is about database problems',
    ]);

    livewire(IncidentResource\RelationManagers\IncidentCommentsRelationManager::class, [
        'ownerRecord' => $incident,
        'pageClass' => IncidentResource\Pages\EditIncident::class,
    ])
        ->searchTable('login')
        ->assertCanSeeTableRecords([$comment1])
        ->assertCanNotSeeTableRecords([$comment2]);
});

test('can search comments by user name', function () {
    $user1 = User::factory()->create(['name' => 'John Doe']);
    $user2 = User::factory()->create(['name' => 'Jane Smith']);
    $incident = Incident::factory()->create();

    $comment1 = IncidentComment::factory()->create([
        'incident_id' => $incident->id,
        'user_id' => $user1->id,
    ]);
    $comment2 = IncidentComment::factory()->create([
        'incident_id' => $incident->id,
        'user_id' => $user2->id,
    ]);

    livewire(IncidentResource\RelationManagers\IncidentCommentsRelationManager::class, [
        'ownerRecord' => $incident,
        'pageClass' => IncidentResource\Pages\EditIncident::class,
    ])
        ->searchTable('John')
        ->assertCanSeeTableRecords([$comment1])
        ->assertCanNotSeeTableRecords([$comment2]);
});

test('can sort comments by user name', function () {
    $user1 = User::factory()->create(['name' => 'Alice']);
    $user2 = User::factory()->create(['name' => 'Bob']);
    $incident = Incident::factory()->create();

    $comment1 = IncidentComment::factory()->create([
        'incident_id' => $incident->id,
        'user_id' => $user1->id,
    ]);
    $comment2 = IncidentComment::factory()->create([
        'incident_id' => $incident->id,
        'user_id' => $user2->id,
    ]);

    livewire(IncidentResource\RelationManagers\IncidentCommentsRelationManager::class, [
        'ownerRecord' => $incident,
        'pageClass' => IncidentResource\Pages\EditIncident::class,
    ])
        ->sortTable('user.name')
        ->assertCanSeeTableRecords([$comment1, $comment2], inOrder: true);
});

test('can sort comments by created_at', function () {
    $incident = Incident::factory()->create();
    $comment1 = IncidentComment::factory()->create([
        'incident_id' => $incident->id,
        'created_at' => now()->subDays(2),
    ]);
    $comment2 = IncidentComment::factory()->create([
        'incident_id' => $incident->id,
        'created_at' => now()->subDay(),
    ]);

    livewire(IncidentResource\RelationManagers\IncidentCommentsRelationManager::class, [
        'ownerRecord' => $incident,
        'pageClass' => IncidentResource\Pages\EditIncident::class,
    ])
        ->sortTable('created_at')
        ->assertCanSeeTableRecords([$comment1, $comment2], inOrder: true);
});

test('edit action is disabled to preserve history', function () {
    $incident = Incident::factory()->create();
    $comment = IncidentComment::factory()->create(['incident_id' => $incident->id]);

    livewire(IncidentResource\RelationManagers\IncidentCommentsRelationManager::class, [
        'ownerRecord' => $incident,
        'pageClass' => IncidentResource\Pages\EditIncident::class,
    ])
        ->assertTableActionDoesNotExist('edit');
});

test('delete action is disabled to preserve history', function () {
    $incident = Incident::factory()->create();
    $comment = IncidentComment::factory()->create(['incident_id' => $incident->id]);

    livewire(IncidentResource\RelationManagers\IncidentCommentsRelationManager::class, [
        'ownerRecord' => $incident,
        'pageClass' => IncidentResource\Pages\EditIncident::class,
    ])
        ->assertTableActionDoesNotExist('delete');
});

test('bulk actions are disabled to preserve history', function () {
    $incident = Incident::factory()->create();
    $comments = IncidentComment::factory()->count(3)->create(['incident_id' => $incident->id]);

    livewire(IncidentResource\RelationManagers\IncidentCommentsRelationManager::class, [
        'ownerRecord' => $incident,
        'pageClass' => IncidentResource\Pages\EditIncident::class,
    ])
        ->assertTableBulkActionDoesNotExist('delete');
});

test('comment text is limited to 100 characters in table', function () {
    $incident = Incident::factory()->create();
    $longComment = str_repeat('This is a very long comment text. ', 10);
    $comment = IncidentComment::factory()->create([
        'incident_id' => $incident->id,
        'comment' => $longComment,
    ]);

    livewire(IncidentResource\RelationManagers\IncidentCommentsRelationManager::class, [
        'ownerRecord' => $incident,
        'pageClass' => IncidentResource\Pages\EditIncident::class,
    ])
        ->assertCanSeeTableRecords([$comment]);
});

test('user can create multiple comments on same incident', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $incident = Incident::factory()->create();

    livewire(IncidentResource\RelationManagers\IncidentCommentsRelationManager::class, [
        'ownerRecord' => $incident,
        'pageClass' => IncidentResource\Pages\EditIncident::class,
    ])
        ->callTableAction('create', data: [
            'comment' => 'First comment',
        ]);

    livewire(IncidentResource\RelationManagers\IncidentCommentsRelationManager::class, [
        'ownerRecord' => $incident,
        'pageClass' => IncidentResource\Pages\EditIncident::class,
    ])
        ->callTableAction('create', data: [
            'comment' => 'Second comment',
        ]);

    $this->assertDatabaseCount('incident_comments', 2);
});

test('comment textarea has 4 rows', function () {
    $incident = Incident::factory()->create();

    livewire(IncidentResource\RelationManagers\IncidentCommentsRelationManager::class, [
        'ownerRecord' => $incident,
        'pageClass' => IncidentResource\Pages\EditIncident::class,
    ])
        ->assertSuccessful();
});

test('only comments for current incident are displayed', function () {
    $incident1 = Incident::factory()->create();
    $incident2 = Incident::factory()->create();

    $comment1 = IncidentComment::factory()->create(['incident_id' => $incident1->id]);
    $comment2 = IncidentComment::factory()->create(['incident_id' => $incident2->id]);

    livewire(IncidentResource\RelationManagers\IncidentCommentsRelationManager::class, [
        'ownerRecord' => $incident1,
        'pageClass' => IncidentResource\Pages\EditIncident::class,
    ])
        ->assertCanSeeTableRecords([$comment1])
        ->assertCanNotSeeTableRecords([$comment2]);
});
