<?php

declare(strict_types=1);

use App\Mail\IncidentCommentMail;
use App\Models\Incident;
use App\Models\IncidentComment;
use App\Models\User;
use Spatie\MailTemplates\TemplateMailable;

test('incident comment mail extends TemplateMailable', function () {
    expect(is_subclass_of(IncidentCommentMail::class, TemplateMailable::class))
        ->toBeTrue();
});

test('incident comment mail has public incident property', function () {
    $incident = Incident::factory()->create();
    $comment = IncidentComment::factory()->create(['incident_id' => $incident->id]);
    $user = $comment->user;

    $mail = new IncidentCommentMail($incident, $comment, $user);

    expect($mail)->toHaveProperty('incident')
        ->and($mail->incident)->toBeInstanceOf(Incident::class)
        ->and($mail->incident->id)->toBe($incident->id);
});

test('incident comment mail has public comment property', function () {
    $incident = Incident::factory()->create();
    $comment = IncidentComment::factory()->create([
        'incident_id' => $incident->id,
        'comment' => 'This is a test comment',
    ]);
    $user = $comment->user;

    $mail = new IncidentCommentMail($incident, $comment, $user);

    expect($mail)->toHaveProperty('comment')
        ->and($mail->comment)->toBeInstanceOf(IncidentComment::class)
        ->and($mail->comment->comment)->toBe('This is a test comment');
});

test('incident comment mail has public user property', function () {
    $incident = Incident::factory()->create();
    $user = User::factory()->create(['name' => 'Jane Smith']);
    $comment = IncidentComment::factory()->create([
        'incident_id' => $incident->id,
        'user_id' => $user->id,
    ]);

    $mail = new IncidentCommentMail($incident, $comment, $user);

    expect($mail)->toHaveProperty('user')
        ->and($mail->user)->toBeInstanceOf(User::class)
        ->and($mail->user->name)->toBe('Jane Smith');
});

test('incident comment mail receives all required parameters in constructor', function () {
    $incident = Incident::factory()->create(['title' => 'Test Incident']);
    $user = User::factory()->create(['name' => 'Commenter User']);
    $comment = IncidentComment::factory()->create([
        'incident_id' => $incident->id,
        'user_id' => $user->id,
        'comment' => 'Great work on this!',
    ]);

    $mail = new IncidentCommentMail($incident, $comment, $user);

    expect($mail->incident->title)->toBe('Test Incident')
        ->and($mail->comment->comment)->toBe('Great work on this!')
        ->and($mail->user->name)->toBe('Commenter User');
});

test('incident comment mail comment has created_at timestamp', function () {
    $incident = Incident::factory()->create();
    $comment = IncidentComment::factory()->create(['incident_id' => $incident->id]);
    $user = $comment->user;

    $mail = new IncidentCommentMail($incident, $comment, $user);

    expect($mail->comment->created_at)->not->toBeNull()
        ->and($mail->comment->created_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});
