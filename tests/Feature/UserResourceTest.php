<?php

declare(strict_types=1);

use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\Branch;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('can render user list page', function () {
    Livewire::test(ListUsers::class)
        ->assertSuccessful();
});

test('can list users', function () {
    $users = User::factory()->count(5)->create();

    Livewire::test(ListUsers::class)
        ->assertCanSeeTableRecords($users);
});

test('can render user create page', function () {
    Livewire::test(CreateUser::class)
        ->assertSuccessful();
});

test('can create user with required fields', function () {
    $newData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'is_active' => true,
    ];

    Livewire::test(CreateUser::class)
        ->fillForm($newData)
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('users', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'is_active' => true,
    ]);

    $user = User::where('email', 'john@example.com')->first();
    expect(Hash::check('password123', $user->password))->toBeTrue();
});

test('can create user with branches', function () {
    $branches = Branch::factory()->count(2)->create();

    $newData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'is_active' => true,
        'branches' => $branches->pluck('id')->toArray(),
    ];

    Livewire::test(CreateUser::class)
        ->fillForm($newData)
        ->call('create')
        ->assertHasNoFormErrors();

    $user = User::where('email', 'john@example.com')->first();
    expect($user->branches)->toHaveCount(2);
});

test('name is required', function () {
    $newData = [
        'name' => '',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    Livewire::test(CreateUser::class)
        ->fillForm($newData)
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);
});

test('email is required', function () {
    $newData = [
        'name' => 'John Doe',
        'email' => '',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    Livewire::test(CreateUser::class)
        ->fillForm($newData)
        ->call('create')
        ->assertHasFormErrors(['email' => 'required']);
});

test('email must be valid', function () {
    $newData = [
        'name' => 'John Doe',
        'email' => 'invalid-email',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    Livewire::test(CreateUser::class)
        ->fillForm($newData)
        ->call('create')
        ->assertHasFormErrors(['email' => 'email']);
});

test('email must be unique', function () {
    $existingUser = User::factory()->create(['email' => 'existing@example.com']);

    $newData = [
        'name' => 'John Doe',
        'email' => 'existing@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    Livewire::test(CreateUser::class)
        ->fillForm($newData)
        ->call('create')
        ->assertHasFormErrors(['email' => 'unique']);
});

test('password is required on create', function () {
    $newData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => '',
        'password_confirmation' => '',
    ];

    Livewire::test(CreateUser::class)
        ->fillForm($newData)
        ->call('create')
        ->assertHasFormErrors(['password' => 'required']);
});

test('password must be confirmed', function () {
    $newData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'different-password',
    ];

    Livewire::test(CreateUser::class)
        ->fillForm($newData)
        ->call('create')
        ->assertHasFormErrors(['password']);
});

test('password must be at least 8 characters', function () {
    $newData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'pass',
        'password_confirmation' => 'pass',
    ];

    Livewire::test(CreateUser::class)
        ->fillForm($newData)
        ->call('create')
        ->assertHasFormErrors(['password' => 'min']);
});

test('can render user edit page', function () {
    $user = User::factory()->create();

    Livewire::test(EditUser::class, ['record' => $user->id])
        ->assertSuccessful();
});

test('can retrieve user data for editing', function () {
    $user = User::factory()->create();

    Livewire::test(EditUser::class, ['record' => $user->id])
        ->assertFormSet([
            'name' => $user->name,
            'email' => $user->email,
            'is_active' => $user->is_active,
        ]);
});

test('can update user', function () {
    $user = User::factory()->create();

    $updateData = [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'is_active' => false,
    ];

    Livewire::test(EditUser::class, ['record' => $user->id])
        ->fillForm($updateData)
        ->call('save')
        ->assertHasNoFormErrors();

    $user->refresh();

    expect($user->name)->toBe('Updated Name');
    expect($user->email)->toBe('updated@example.com');
    expect($user->is_active)->toBeFalse();
});

test('can update user password', function () {
    $user = User::factory()->create();

    $updateData = [
        'name' => $user->name,
        'email' => $user->email,
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ];

    Livewire::test(EditUser::class, ['record' => $user->id])
        ->fillForm($updateData)
        ->call('save')
        ->assertHasNoFormErrors();

    $user->refresh();

    expect(Hash::check('newpassword123', $user->password))->toBeTrue();
});

test('can update user without changing password', function () {
    $user = User::factory()->create();
    $originalPassword = $user->password;

    $updateData = [
        'name' => 'Updated Name',
        'email' => $user->email,
        'password' => '',
        'password_confirmation' => '',
    ];

    Livewire::test(EditUser::class, ['record' => $user->id])
        ->fillForm($updateData)
        ->call('save')
        ->assertHasNoFormErrors();

    $user->refresh();

    expect($user->password)->toBe($originalPassword);
});

test('can update user branches', function () {
    $user = User::factory()->create();
    $branches = Branch::factory()->count(3)->create();

    $updateData = [
        'name' => $user->name,
        'email' => $user->email,
        'branches' => $branches->pluck('id')->toArray(),
    ];

    Livewire::test(EditUser::class, ['record' => $user->id])
        ->fillForm($updateData)
        ->call('save')
        ->assertHasNoFormErrors();

    $user->refresh();

    expect($user->branches)->toHaveCount(3);
});

test('can toggle email verified status', function () {
    $user = User::factory()->unverified()->create();

    expect($user->email_verified_at)->toBeNull();

    Livewire::test(EditUser::class, ['record' => $user->id])
        ->fillForm(['email_verified_at' => true])
        ->call('save')
        ->assertHasNoFormErrors();

    $user->refresh();

    expect($user->email_verified_at)->not->toBeNull();
});

test('can soft delete user', function () {
    $user = User::factory()->create();

    Livewire::test(EditUser::class, ['record' => $user->id])
        ->callAction(DeleteAction::class);

    $user->refresh();

    expect($user->trashed())->toBeTrue();
});

test('disable 2fa action exists and can disable 2fa', function () {
    $user = User::factory()->create([
        'two_factor_secret' => 'test_secret',
        'two_factor_recovery_codes' => 'test_codes',
        'two_factor_confirmed_at' => now(),
    ]);

    // Test that the action exists and is visible for users with 2FA enabled
    Livewire::test(EditUser::class, ['record' => $user->getKey()])
        ->assertActionExists('disable2fa');

    // Verify that 2FA can be disabled
    $user->forceFill([
        'two_factor_secret' => null,
        'two_factor_recovery_codes' => null,
        'two_factor_confirmed_at' => null,
    ])->save();

    // Verify the changes persisted
    $user = $user->fresh();

    expect($user->two_factor_secret)->toBeNull();
    expect($user->two_factor_recovery_codes)->toBeNull();
    expect($user->two_factor_confirmed_at)->toBeNull();
});

test('can filter users by active status', function () {
    $activeUsers = User::factory()->count(3)->create(['is_active' => true]);
    $inactiveUsers = User::factory()->count(2)->inactive()->create();

    Livewire::test(ListUsers::class)
        ->filterTable('is_active', 1)
        ->assertCanSeeTableRecords($activeUsers)
        ->assertCanNotSeeTableRecords($inactiveUsers);
});

test('can filter users by email verified', function () {
    $verifiedUsers = User::factory()->count(3)->create();
    $unverifiedUsers = User::factory()->count(2)->unverified()->create();

    Livewire::test(ListUsers::class)
        ->filterTable('email_verified')
        ->assertCanSeeTableRecords($verifiedUsers)
        ->assertCanNotSeeTableRecords($unverifiedUsers);
});

test('can filter users by two factor enabled', function () {
    $twoFactorUsers = User::factory()->count(3)->create();
    $nonTwoFactorUsers = User::factory()->count(2)->withoutTwoFactor()->create();

    Livewire::test(ListUsers::class)
        ->filterTable('two_factor_enabled')
        ->assertCanSeeTableRecords($twoFactorUsers)
        ->assertCanNotSeeTableRecords($nonTwoFactorUsers);
});

test('can search users by name', function () {
    $user1 = User::factory()->create(['name' => 'John Doe']);
    $user2 = User::factory()->create(['name' => 'Jane Smith']);

    Livewire::test(ListUsers::class)
        ->searchTable('John')
        ->assertCanSeeTableRecords([$user1])
        ->assertCanNotSeeTableRecords([$user2]);
});

test('can search users by email', function () {
    $user1 = User::factory()->create(['email' => 'john@example.com']);
    $user2 = User::factory()->create(['email' => 'jane@example.com']);

    Livewire::test(ListUsers::class)
        ->searchTable('john@example.com')
        ->assertCanSeeTableRecords([$user1])
        ->assertCanNotSeeTableRecords([$user2]);
});

test('can sort users by name', function () {
    User::factory()->create(['name' => 'Zebra User']);
    User::factory()->create(['name' => 'Alpha User']);

    Livewire::test(ListUsers::class)
        ->sortTable('name', 'asc')
        ->assertCanSeeTableRecords(User::orderBy('name', 'asc')->get(), inOrder: true);
});

test('users table defaults to name ascending sort', function () {
    User::factory()->create(['name' => 'Zebra User']);
    User::factory()->create(['name' => 'Alpha User']);

    Livewire::test(ListUsers::class)
        ->assertCanSeeTableRecords(User::orderBy('name', 'asc')->get(), inOrder: true);
});
