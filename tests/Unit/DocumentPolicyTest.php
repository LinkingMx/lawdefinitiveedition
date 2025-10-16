<?php

declare(strict_types=1);

use App\Models\Document;
use App\Models\User;
use App\Policies\DocumentPolicy;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create permissions
    Permission::create(['name' => 'view_any_document']);
    Permission::create(['name' => 'view_document']);
    Permission::create(['name' => 'create_document']);
    Permission::create(['name' => 'update_document']);
    Permission::create(['name' => 'delete_document']);
    Permission::create(['name' => 'delete_any_document']);
    Permission::create(['name' => 'force_delete_document']);
    Permission::create(['name' => 'force_delete_any_document']);
    Permission::create(['name' => 'restore_document']);
    Permission::create(['name' => 'restore_any_document']);
    Permission::create(['name' => 'replicate_document']);
    Permission::create(['name' => 'reorder_document']);
});

test('policy allows user with view_any_document permission to view any documents', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('view_any_document');

    $policy = new DocumentPolicy;

    expect($policy->viewAny($user))->toBeTrue();
});

test('policy denies user without view_any_document permission to view any documents', function () {
    $user = User::factory()->create();

    $policy = new DocumentPolicy;

    expect($policy->viewAny($user))->toBeFalse();
});

test('policy allows user with view_document permission to view a document', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('view_document');
    $document = Document::factory()->create();

    $policy = new DocumentPolicy;

    expect($policy->view($user, $document))->toBeTrue();
});

test('policy denies user without view_document permission to view a document', function () {
    $user = User::factory()->create();
    $document = Document::factory()->create();

    $policy = new DocumentPolicy;

    expect($policy->view($user, $document))->toBeFalse();
});

test('policy allows user with create_document permission to create documents', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('create_document');

    $policy = new DocumentPolicy;

    expect($policy->create($user))->toBeTrue();
});

test('policy denies user without create_document permission to create documents', function () {
    $user = User::factory()->create();

    $policy = new DocumentPolicy;

    expect($policy->create($user))->toBeFalse();
});

test('policy allows user with update_document permission to update a document', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('update_document');
    $document = Document::factory()->create();

    $policy = new DocumentPolicy;

    expect($policy->update($user, $document))->toBeTrue();
});

test('policy denies user without update_document permission to update a document', function () {
    $user = User::factory()->create();
    $document = Document::factory()->create();

    $policy = new DocumentPolicy;

    expect($policy->update($user, $document))->toBeFalse();
});

test('policy allows user with delete_document permission to delete a document', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('delete_document');
    $document = Document::factory()->create();

    $policy = new DocumentPolicy;

    expect($policy->delete($user, $document))->toBeTrue();
});

test('policy denies user without delete_document permission to delete a document', function () {
    $user = User::factory()->create();
    $document = Document::factory()->create();

    $policy = new DocumentPolicy;

    expect($policy->delete($user, $document))->toBeFalse();
});

test('policy allows user with delete_any_document permission to bulk delete', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('delete_any_document');

    $policy = new DocumentPolicy;

    expect($policy->deleteAny($user))->toBeTrue();
});

test('policy denies user without delete_any_document permission to bulk delete', function () {
    $user = User::factory()->create();

    $policy = new DocumentPolicy;

    expect($policy->deleteAny($user))->toBeFalse();
});

test('policy allows user with force_delete_document permission to force delete a document', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('force_delete_document');
    $document = Document::factory()->create();

    $policy = new DocumentPolicy;

    expect($policy->forceDelete($user, $document))->toBeTrue();
});

test('policy denies user without force_delete_document permission to force delete a document', function () {
    $user = User::factory()->create();
    $document = Document::factory()->create();

    $policy = new DocumentPolicy;

    expect($policy->forceDelete($user, $document))->toBeFalse();
});

test('policy allows user with force_delete_any_document permission to bulk force delete', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('force_delete_any_document');

    $policy = new DocumentPolicy;

    expect($policy->forceDeleteAny($user))->toBeTrue();
});

test('policy denies user without force_delete_any_document permission to bulk force delete', function () {
    $user = User::factory()->create();

    $policy = new DocumentPolicy;

    expect($policy->forceDeleteAny($user))->toBeFalse();
});

test('policy allows user with restore_document permission to restore a document', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('restore_document');
    $document = Document::factory()->create();

    $policy = new DocumentPolicy;

    expect($policy->restore($user, $document))->toBeTrue();
});

test('policy denies user without restore_document permission to restore a document', function () {
    $user = User::factory()->create();
    $document = Document::factory()->create();

    $policy = new DocumentPolicy;

    expect($policy->restore($user, $document))->toBeFalse();
});

test('policy allows user with restore_any_document permission to bulk restore', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('restore_any_document');

    $policy = new DocumentPolicy;

    expect($policy->restoreAny($user))->toBeTrue();
});

test('policy denies user without restore_any_document permission to bulk restore', function () {
    $user = User::factory()->create();

    $policy = new DocumentPolicy;

    expect($policy->restoreAny($user))->toBeFalse();
});

test('policy allows user with replicate_document permission to replicate a document', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('replicate_document');
    $document = Document::factory()->create();

    $policy = new DocumentPolicy;

    expect($policy->replicate($user, $document))->toBeTrue();
});

test('policy denies user without replicate_document permission to replicate a document', function () {
    $user = User::factory()->create();
    $document = Document::factory()->create();

    $policy = new DocumentPolicy;

    expect($policy->replicate($user, $document))->toBeFalse();
});

test('policy allows user with reorder_document permission to reorder documents', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('reorder_document');

    $policy = new DocumentPolicy;

    expect($policy->reorder($user))->toBeTrue();
});

test('policy denies user without reorder_document permission to reorder documents', function () {
    $user = User::factory()->create();

    $policy = new DocumentPolicy;

    expect($policy->reorder($user))->toBeFalse();
});

test('user with admin role has all document permissions', function () {
    $adminRole = Role::create(['name' => 'admin']);
    $adminRole->givePermissionTo([
        'view_any_document',
        'view_document',
        'create_document',
        'update_document',
        'delete_document',
        'delete_any_document',
        'force_delete_document',
        'force_delete_any_document',
        'restore_document',
        'restore_any_document',
        'replicate_document',
        'reorder_document',
    ]);

    $user = User::factory()->create();
    $user->assignRole('admin');
    $document = Document::factory()->create();

    $policy = new DocumentPolicy;

    expect($policy->viewAny($user))->toBeTrue()
        ->and($policy->view($user, $document))->toBeTrue()
        ->and($policy->create($user))->toBeTrue()
        ->and($policy->update($user, $document))->toBeTrue()
        ->and($policy->delete($user, $document))->toBeTrue()
        ->and($policy->deleteAny($user))->toBeTrue()
        ->and($policy->forceDelete($user, $document))->toBeTrue()
        ->and($policy->forceDeleteAny($user))->toBeTrue()
        ->and($policy->restore($user, $document))->toBeTrue()
        ->and($policy->restoreAny($user))->toBeTrue()
        ->and($policy->replicate($user, $document))->toBeTrue()
        ->and($policy->reorder($user))->toBeTrue();
});

test('user with viewer role has only view permissions', function () {
    $viewerRole = Role::create(['name' => 'viewer']);
    $viewerRole->givePermissionTo(['view_any_document', 'view_document']);

    $user = User::factory()->create();
    $user->assignRole('viewer');
    $document = Document::factory()->create();

    $policy = new DocumentPolicy;

    expect($policy->viewAny($user))->toBeTrue()
        ->and($policy->view($user, $document))->toBeTrue()
        ->and($policy->create($user))->toBeFalse()
        ->and($policy->update($user, $document))->toBeFalse()
        ->and($policy->delete($user, $document))->toBeFalse();
});

test('policy uses HandlesAuthorization trait', function () {
    $traits = class_uses(DocumentPolicy::class);

    expect($traits)->toHaveKey('Illuminate\Auth\Access\HandlesAuthorization');
});
