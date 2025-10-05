<?php

declare(strict_types=1);

use App\Models\Branch;
use App\Models\User;

test('user has fillable attributes', function () {
    $fillable = (new User)->getFillable();

    expect($fillable)->toContain('name');
    expect($fillable)->toContain('email');
    expect($fillable)->toContain('password');
    expect($fillable)->toContain('is_active');
    expect($fillable)->toContain('avatar');
    expect($fillable)->toContain('last_login_at');
    expect($fillable)->toContain('email_verified_at');
});

test('user has hidden attributes', function () {
    $hidden = (new User)->getHidden();

    expect($hidden)->toContain('password');
    expect($hidden)->toContain('remember_token');
});

test('user casts attributes correctly', function () {
    $user = User::factory()->create();

    expect($user->is_active)->toBeBool();
    expect($user->email_verified_at)->toBeInstanceOf(DateTime::class);
    expect($user->last_login_at)->toBeInstanceOf(DateTime::class);
});

test('user has branches relationship', function () {
    $user = User::factory()->create();
    $branch = Branch::factory()->create();

    $user->branches()->attach($branch->id);

    expect($user->branches)->toHaveCount(1);
    expect($user->branches->first()->id)->toBe($branch->id);
});

test('user can have multiple branches', function () {
    $user = User::factory()->create();
    $branches = Branch::factory()->count(3)->create();

    $user->branches()->attach($branches->pluck('id'));

    expect($user->branches)->toHaveCount(3);
});

test('user can be soft deleted', function () {
    $user = User::factory()->create();

    $user->delete();

    expect($user->trashed())->toBeTrue();
    expect(User::withTrashed()->find($user->id))->not->toBeNull();
});

test('soft deleted user can be restored', function () {
    $user = User::factory()->create();
    $user->delete();

    $user->restore();

    expect($user->trashed())->toBeFalse();
    expect(User::find($user->id))->not->toBeNull();
});

test('user can be force deleted', function () {
    $user = User::factory()->create();
    $userId = $user->id;

    $user->forceDelete();

    expect(User::withTrashed()->find($userId))->toBeNull();
});

test('user factory creates active users by default', function () {
    $user = User::factory()->create();

    expect($user->is_active)->toBeTrue();
});

test('user factory can create inactive users', function () {
    $user = User::factory()->inactive()->create();

    expect($user->is_active)->toBeFalse();
});

test('user factory creates users with avatar by default', function () {
    $user = User::factory()->create();

    expect($user->avatar)->not->toBeNull();
    expect($user->avatar)->toContain('avatars/');
});

test('user factory can create users without avatar', function () {
    $user = User::factory()->withoutAvatar()->create();

    expect($user->avatar)->toBeNull();
});

test('user factory can create users who never logged in', function () {
    $user = User::factory()->neverLoggedIn()->create();

    expect($user->last_login_at)->toBeNull();
});

test('user factory creates verified users by default', function () {
    $user = User::factory()->create();

    expect($user->email_verified_at)->not->toBeNull();
});

test('user factory can create unverified users', function () {
    $user = User::factory()->unverified()->create();

    expect($user->email_verified_at)->toBeNull();
});

test('user factory creates users with 2fa enabled by default', function () {
    $user = User::factory()->create();

    expect($user->two_factor_confirmed_at)->not->toBeNull();
});

test('user factory can create users without 2fa', function () {
    $user = User::factory()->withoutTwoFactor()->create();

    expect($user->two_factor_secret)->toBeNull();
    expect($user->two_factor_recovery_codes)->toBeNull();
    expect($user->two_factor_confirmed_at)->toBeNull();
});
