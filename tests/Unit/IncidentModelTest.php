<?php

declare(strict_types=1);

use App\Models\Branch;
use App\Models\Document;
use App\Models\Incident;
use App\Models\IncidentComment;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

test('incident model uses SoftDeletes trait', function () {
    $traits = class_uses(Incident::class);

    expect($traits)->toHaveKey(SoftDeletes::class);
});

test('incident has fillable attributes', function () {
    $incident = new Incident;

    $fillable = $incident->getFillable();

    expect($fillable)->toBe([
        'title',
        'description',
        'branch_id',
        'document_id',
        'user_id',
        'status',
        'priority',
    ]);
});

test('incident casts status to string', function () {
    $incident = Incident::factory()->create([
        'status' => 'open',
    ]);

    expect($incident->status)
        ->toBeString()
        ->toBe('open');
});

test('incident casts priority to string', function () {
    $incident = Incident::factory()->create([
        'priority' => 'high',
    ]);

    expect($incident->priority)
        ->toBeString()
        ->toBe('high');
});

test('incident has branch relationship', function () {
    $incident = new Incident;

    expect($incident->branch())
        ->toBeInstanceOf(BelongsTo::class);
});

test('incident belongs to branch', function () {
    $branch = Branch::factory()->create(['name' => 'Main Office']);
    $incident = Incident::factory()->create(['branch_id' => $branch->id]);

    expect($incident->branch)
        ->toBeInstanceOf(Branch::class)
        ->and($incident->branch->id)->toBe($branch->id)
        ->and($incident->branch->name)->toBe('Main Office');
});

test('incident has document relationship', function () {
    $incident = new Incident;

    expect($incident->document())
        ->toBeInstanceOf(BelongsTo::class);
});

test('incident can belong to a document', function () {
    $document = Document::factory()->create();
    $incident = Incident::factory()->create(['document_id' => $document->id]);

    expect($incident->document)
        ->toBeInstanceOf(Document::class)
        ->and($incident->document->id)->toBe($document->id);
});

test('incident document relationship is nullable', function () {
    $incident = Incident::factory()->create(['document_id' => null]);

    expect($incident->document)->toBeNull();
});

test('incident has reporter relationship', function () {
    $incident = new Incident;

    expect($incident->reporter())
        ->toBeInstanceOf(BelongsTo::class);
});

test('incident belongs to reporter user', function () {
    $user = User::factory()->create(['name' => 'John Doe']);
    $incident = Incident::factory()->create(['user_id' => $user->id]);

    expect($incident->reporter)
        ->toBeInstanceOf(User::class)
        ->and($incident->reporter->id)->toBe($user->id)
        ->and($incident->reporter->name)->toBe('John Doe');
});

test('incident has comments relationship', function () {
    $incident = new Incident;

    expect($incident->comments())
        ->toBeInstanceOf(HasMany::class);
});

test('incident has many comments', function () {
    $incident = Incident::factory()->create();
    $comment1 = IncidentComment::factory()->create(['incident_id' => $incident->id]);
    $comment2 = IncidentComment::factory()->create(['incident_id' => $incident->id]);

    expect($incident->comments)
        ->toHaveCount(2)
        ->and($incident->comments->first())->toBeInstanceOf(IncidentComment::class);
});

test('incident has timestamps', function () {
    $incident = Incident::factory()->create();

    expect($incident->created_at)->not->toBeNull()
        ->and($incident->updated_at)->not->toBeNull();
});

test('incident can be soft deleted', function () {
    $incident = Incident::factory()->create();

    $incident->delete();

    expect($incident->deleted_at)->not->toBeNull()
        ->and(Incident::withTrashed()->find($incident->id))->not->toBeNull();
});

test('incident can be restored after soft delete', function () {
    $incident = Incident::factory()->create();
    $incident->delete();

    $incident->restore();

    expect($incident->deleted_at)->toBeNull()
        ->and(Incident::find($incident->id))->not->toBeNull();
});

test('incident can be force deleted', function () {
    $incident = Incident::factory()->create();
    $incidentId = $incident->id;

    $incident->forceDelete();

    expect(Incident::withTrashed()->find($incidentId))->toBeNull();
});

test('incident updates updated_at timestamp on save', function () {
    $incident = Incident::factory()->create();
    $originalUpdatedAt = $incident->updated_at;

    sleep(1);
    $incident->description = 'Updated description';
    $incident->save();

    expect($incident->updated_at->isAfter($originalUpdatedAt))->toBeTrue();
});

test('incident preserves created_at timestamp on update', function () {
    $incident = Incident::factory()->create();
    $originalCreatedAt = $incident->created_at;

    $incident->description = 'Updated description';
    $incident->save();

    expect($incident->created_at->equalTo($originalCreatedAt))->toBeTrue();
});

test('factory creates valid incident', function () {
    $incident = Incident::factory()->create();

    expect($incident)->toBeInstanceOf(Incident::class)
        ->and($incident->title)->toBeString()
        ->and($incident->description)->toBeString()
        ->and($incident->branch_id)->toBeInt()
        ->and($incident->user_id)->toBeInt()
        ->and($incident->status)->toBeString()
        ->and($incident->priority)->toBeString();
});

test('factory creates incident with valid branch relationship', function () {
    $incident = Incident::factory()->create();

    expect($incident->branch)->toBeInstanceOf(Branch::class);
});

test('factory creates incident with valid reporter relationship', function () {
    $incident = Incident::factory()->create();

    expect($incident->reporter)->toBeInstanceOf(User::class);
});

test('incident status defaults to open', function () {
    $incident = Incident::factory()->create(['status' => 'open']);

    expect($incident->status)->toBe('open');
});

test('incident priority defaults to medium', function () {
    $incident = Incident::factory()->create(['priority' => 'medium']);

    expect($incident->priority)->toBe('medium');
});

test('incident can have status open', function () {
    $incident = Incident::factory()->create(['status' => 'open']);

    expect($incident->status)->toBe('open');
});

test('incident can have status in_progress', function () {
    $incident = Incident::factory()->create(['status' => 'in_progress']);

    expect($incident->status)->toBe('in_progress');
});

test('incident can have status resolved', function () {
    $incident = Incident::factory()->create(['status' => 'resolved']);

    expect($incident->status)->toBe('resolved');
});

test('incident can have status closed', function () {
    $incident = Incident::factory()->create(['status' => 'closed']);

    expect($incident->status)->toBe('closed');
});

test('incident can have priority low', function () {
    $incident = Incident::factory()->create(['priority' => 'low']);

    expect($incident->priority)->toBe('low');
});

test('incident can have priority medium', function () {
    $incident = Incident::factory()->create(['priority' => 'medium']);

    expect($incident->priority)->toBe('medium');
});

test('incident can have priority high', function () {
    $incident = Incident::factory()->create(['priority' => 'high']);

    expect($incident->priority)->toBe('high');
});

test('incident title is stored correctly', function () {
    $incident = Incident::factory()->create(['title' => 'System Error on Login']);

    expect($incident->title)->toBe('System Error on Login');
});

test('incident description is stored correctly', function () {
    $incident = Incident::factory()->create(['description' => 'Users unable to login']);

    expect($incident->description)->toBe('Users unable to login');
});
