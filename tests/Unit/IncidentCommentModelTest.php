<?php

declare(strict_types=1);

use App\Models\Incident;
use App\Models\IncidentComment;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

test('incident comment model uses SoftDeletes trait', function () {
    $traits = class_uses(IncidentComment::class);

    expect($traits)->toHaveKey(SoftDeletes::class);
});

test('incident comment has fillable attributes', function () {
    $comment = new IncidentComment;

    $fillable = $comment->getFillable();

    expect($fillable)->toBe([
        'incident_id',
        'user_id',
        'comment',
    ]);
});

test('incident comment has incident relationship', function () {
    $comment = new IncidentComment;

    expect($comment->incident())
        ->toBeInstanceOf(BelongsTo::class);
});

test('incident comment belongs to incident', function () {
    $incident = Incident::factory()->create(['title' => 'System Error']);
    $comment = IncidentComment::factory()->create(['incident_id' => $incident->id]);

    expect($comment->incident)
        ->toBeInstanceOf(Incident::class)
        ->and($comment->incident->id)->toBe($incident->id)
        ->and($comment->incident->title)->toBe('System Error');
});

test('incident comment has user relationship', function () {
    $comment = new IncidentComment;

    expect($comment->user())
        ->toBeInstanceOf(BelongsTo::class);
});

test('incident comment belongs to user', function () {
    $user = User::factory()->create(['name' => 'Jane Doe']);
    $comment = IncidentComment::factory()->create(['user_id' => $user->id]);

    expect($comment->user)
        ->toBeInstanceOf(User::class)
        ->and($comment->user->id)->toBe($user->id)
        ->and($comment->user->name)->toBe('Jane Doe');
});

test('incident comment has timestamps', function () {
    $comment = IncidentComment::factory()->create();

    expect($comment->created_at)->not->toBeNull()
        ->and($comment->updated_at)->not->toBeNull();
});

test('incident comment can be soft deleted', function () {
    $comment = IncidentComment::factory()->create();

    $comment->delete();

    expect($comment->deleted_at)->not->toBeNull()
        ->and(IncidentComment::withTrashed()->find($comment->id))->not->toBeNull();
});

test('incident comment can be restored after soft delete', function () {
    $comment = IncidentComment::factory()->create();
    $comment->delete();

    $comment->restore();

    expect($comment->deleted_at)->toBeNull()
        ->and(IncidentComment::find($comment->id))->not->toBeNull();
});

test('incident comment can be force deleted', function () {
    $comment = IncidentComment::factory()->create();
    $commentId = $comment->id;

    $comment->forceDelete();

    expect(IncidentComment::withTrashed()->find($commentId))->toBeNull();
});

test('incident comment updates updated_at timestamp on save', function () {
    $comment = IncidentComment::factory()->create();
    $originalUpdatedAt = $comment->updated_at;

    sleep(1);
    $comment->comment = 'Updated comment text';
    $comment->save();

    expect($comment->updated_at->isAfter($originalUpdatedAt))->toBeTrue();
});

test('incident comment preserves created_at timestamp on update', function () {
    $comment = IncidentComment::factory()->create();
    $originalCreatedAt = $comment->created_at;

    $comment->comment = 'Updated comment text';
    $comment->save();

    expect($comment->created_at->equalTo($originalCreatedAt))->toBeTrue();
});

test('factory creates valid incident comment', function () {
    $comment = IncidentComment::factory()->create();

    expect($comment)->toBeInstanceOf(IncidentComment::class)
        ->and($comment->incident_id)->toBeInt()
        ->and($comment->user_id)->toBeInt()
        ->and($comment->comment)->toBeString();
});

test('factory creates incident comment with valid incident relationship', function () {
    $comment = IncidentComment::factory()->create();

    expect($comment->incident)->toBeInstanceOf(Incident::class);
});

test('factory creates incident comment with valid user relationship', function () {
    $comment = IncidentComment::factory()->create();

    expect($comment->user)->toBeInstanceOf(User::class);
});

test('incident comment stores text correctly', function () {
    $commentText = 'This is a test comment for the incident';
    $comment = IncidentComment::factory()->create(['comment' => $commentText]);

    expect($comment->comment)->toBe($commentText);
});

test('incident comment can store long text', function () {
    $longComment = str_repeat('This is a very long comment. ', 50);
    $comment = IncidentComment::factory()->create(['comment' => $longComment]);

    expect($comment->comment)
        ->toBe($longComment)
        ->and(strlen($comment->comment))->toBeGreaterThan(500);
});
