<?php

declare(strict_types=1);

use App\Filament\Resources\BranchResource;
use App\Filament\Resources\BranchResource\Pages\CreateBranch;
use App\Filament\Resources\BranchResource\Pages\EditBranch;
use App\Filament\Resources\BranchResource\Pages\ListBranches;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\assertSoftDeleted;

describe('Branch Filament Resource', function () {
    beforeEach(function () {
        // Create and authenticate a user for all tests
        $this->actingAs(User::factory()->create());
    });

    describe('List Page', function () {
        it('can render the list page', function () {
            Livewire::test(ListBranches::class)
                ->assertSuccessful();
        });

        it('can list branches', function () {
            $branches = Branch::factory()->count(5)->create();

            Livewire::test(ListBranches::class)
                ->assertCanSeeTableRecords($branches);
        });

        it('displays branches sorted by name by default', function () {
            $alpha = Branch::factory()->create(['name' => 'Alpha Branch']);
            $mike = Branch::factory()->create(['name' => 'Mike Branch']);
            $zulu = Branch::factory()->create(['name' => 'Zulu Branch']);

            Livewire::test(ListBranches::class)
                ->assertCanSeeTableRecords([$alpha, $mike, $zulu], inOrder: true);
        });

        it('can search branches by name', function () {
            $branch1 = Branch::factory()->create(['name' => 'Downtown Office']);
            $branch2 = Branch::factory()->create(['name' => 'Uptown Branch']);

            Livewire::test(ListBranches::class)
                ->searchTable('Downtown')
                ->assertCanSeeTableRecords([$branch1])
                ->assertCanNotSeeTableRecords([$branch2]);
        });

        it('can sort branches by name', function () {
            $branches = Branch::factory()->count(3)->create();

            Livewire::test(ListBranches::class)
                ->sortTable('name')
                ->assertCanSeeTableRecords($branches->sortBy('name'), inOrder: true)
                ->sortTable('name', 'desc')
                ->assertCanSeeTableRecords($branches->sortByDesc('name'), inOrder: true);
        });

        it('can sort branches by other columns', function () {
            $branches = Branch::factory()->count(3)->create();

            Livewire::test(ListBranches::class)
                ->sortTable('contact_name')
                ->assertSuccessful()
                ->sortTable('contact_email')
                ->assertSuccessful()
                ->sortTable('created_at')
                ->assertSuccessful();
        });

        it('can filter trashed branches', function () {
            $activeBranch = Branch::factory()->create(['name' => 'Active Branch']);
            $deletedBranch = Branch::factory()->create(['name' => 'Deleted Branch']);
            $deletedBranch->delete();

            // Test default view - should see active only
            Livewire::test(ListBranches::class)
                ->assertCanSeeTableRecords([$activeBranch]);

            // Test trashed only
            Livewire::test(ListBranches::class)
                ->filterTable('trashed', 'only')
                ->assertCanSeeTableRecords([$deletedBranch]);

            // Test with trashed - should see both
            Livewire::test(ListBranches::class)
                ->filterTable('trashed', 'with')
                ->assertSuccessful();

            // Verify database state
            assertDatabaseHas('branches', [
                'id' => $activeBranch->id,
                'deleted_at' => null,
            ]);

            assertSoftDeleted('branches', [
                'id' => $deletedBranch->id,
            ]);
        });

        it('has create action in header', function () {
            Livewire::test(ListBranches::class)
                ->assertActionExists('create');
        });
    });

    describe('Create Page', function () {
        it('can render the create page', function () {
            Livewire::test(CreateBranch::class)
                ->assertSuccessful();
        });

        it('can create a branch with required fields only', function () {
            $newData = [
                'name' => 'New Downtown Branch',
            ];

            Livewire::test(CreateBranch::class)
                ->fillForm($newData)
                ->call('create')
                ->assertHasNoFormErrors();

            assertDatabaseHas('branches', $newData);
        });

        it('can create a branch with all fields', function () {
            $newData = [
                'name' => 'Main Office',
                'address' => '123 Main St, New York, NY 10001',
                'contact_name' => 'John Doe',
                'contact_email' => 'john@example.com',
                'contact_phone' => '+1-555-1234',
            ];

            Livewire::test(CreateBranch::class)
                ->fillForm($newData)
                ->call('create')
                ->assertHasNoFormErrors();

            assertDatabaseHas('branches', $newData);
        });

        it('validates required name field', function () {
            Livewire::test(CreateBranch::class)
                ->fillForm([
                    'name' => null,
                ])
                ->call('create')
                ->assertHasFormErrors(['name' => 'required']);

            assertDatabaseCount('branches', 0);
        });

        it('validates email format', function () {
            Livewire::test(CreateBranch::class)
                ->fillForm([
                    'name' => 'Test Branch',
                    'contact_email' => 'invalid-email',
                ])
                ->call('create')
                ->assertHasFormErrors(['contact_email' => 'email']);

            assertDatabaseCount('branches', 0);
        });

        it('validates maximum length for name field', function () {
            Livewire::test(CreateBranch::class)
                ->fillForm([
                    'name' => Str::random(256),
                ])
                ->call('create')
                ->assertHasFormErrors(['name' => 'max']);

            assertDatabaseCount('branches', 0);
        });

        it('validates maximum length for address field', function () {
            Livewire::test(CreateBranch::class)
                ->fillForm([
                    'name' => 'Test Branch',
                    'address' => Str::random(501),
                ])
                ->call('create')
                ->assertHasFormErrors(['address' => 'max']);

            assertDatabaseCount('branches', 0);
        });

        it('validates maximum length for contact fields', function () {
            Livewire::test(CreateBranch::class)
                ->fillForm([
                    'name' => 'Test Branch',
                    'contact_name' => Str::random(256),
                ])
                ->call('create')
                ->assertHasFormErrors(['contact_name' => 'max']);

            Livewire::test(CreateBranch::class)
                ->fillForm([
                    'name' => 'Test Branch',
                    'contact_email' => Str::random(250).'@test.com',
                ])
                ->call('create')
                ->assertHasFormErrors(['contact_email' => 'max']);

            Livewire::test(CreateBranch::class)
                ->fillForm([
                    'name' => 'Test Branch',
                    'contact_phone' => Str::random(256),
                ])
                ->call('create')
                ->assertHasFormErrors(['contact_phone' => 'max']);

            assertDatabaseCount('branches', 0);
        });

        it('redirects to index page after successful creation', function () {
            Livewire::test(CreateBranch::class)
                ->fillForm([
                    'name' => 'Test Branch',
                ])
                ->call('create')
                ->assertRedirect(BranchResource::getUrl('index'));
        });

        it('sends success notification after creation', function () {
            Livewire::test(CreateBranch::class)
                ->fillForm([
                    'name' => 'Test Branch',
                ])
                ->call('create')
                ->assertNotified();
        });
    });

    describe('Edit Page', function () {
        it('can render the edit page', function () {
            $branch = Branch::factory()->create();

            Livewire::test(EditBranch::class, [
                'record' => $branch->getRouteKey(),
            ])
                ->assertSuccessful();
        });

        it('can retrieve branch data for editing', function () {
            $branch = Branch::factory()->create();

            Livewire::test(EditBranch::class, [
                'record' => $branch->getRouteKey(),
            ])
                ->assertFormSet([
                    'name' => $branch->name,
                    'address' => $branch->address,
                    'contact_name' => $branch->contact_name,
                    'contact_email' => $branch->contact_email,
                    'contact_phone' => $branch->contact_phone,
                ]);
        });

        it('can update a branch', function () {
            $branch = Branch::factory()->create();
            $newData = [
                'name' => 'Updated Branch Name',
                'address' => 'Updated Address',
                'contact_name' => 'Jane Smith',
                'contact_email' => 'jane@example.com',
                'contact_phone' => '+1-555-5678',
            ];

            Livewire::test(EditBranch::class, [
                'record' => $branch->getRouteKey(),
            ])
                ->fillForm($newData)
                ->call('save')
                ->assertHasNoFormErrors();

            expect($branch->refresh())
                ->name->toBe($newData['name'])
                ->address->toBe($newData['address'])
                ->contact_name->toBe($newData['contact_name'])
                ->contact_email->toBe($newData['contact_email'])
                ->contact_phone->toBe($newData['contact_phone']);
        });

        it('validates required name field on update', function () {
            $branch = Branch::factory()->create();

            Livewire::test(EditBranch::class, [
                'record' => $branch->getRouteKey(),
            ])
                ->fillForm([
                    'name' => null,
                ])
                ->call('save')
                ->assertHasFormErrors(['name' => 'required']);
        });

        it('validates email format on update', function () {
            $branch = Branch::factory()->create();

            Livewire::test(EditBranch::class, [
                'record' => $branch->getRouteKey(),
            ])
                ->fillForm([
                    'contact_email' => 'invalid-email',
                ])
                ->call('save')
                ->assertHasFormErrors(['contact_email' => 'email']);
        });

        it('can update branch to have null optional fields', function () {
            $branch = Branch::factory()->create();

            Livewire::test(EditBranch::class, [
                'record' => $branch->getRouteKey(),
            ])
                ->fillForm([
                    'name' => 'Minimal Branch',
                    'address' => null,
                    'contact_name' => null,
                    'contact_email' => null,
                    'contact_phone' => null,
                ])
                ->call('save')
                ->assertHasNoFormErrors();

            expect($branch->refresh())
                ->name->toBe('Minimal Branch')
                ->address->toBeNull()
                ->contact_name->toBeNull()
                ->contact_email->toBeNull()
                ->contact_phone->toBeNull();
        });

        it('redirects to index page after successful update', function () {
            $branch = Branch::factory()->create();

            Livewire::test(EditBranch::class, [
                'record' => $branch->getRouteKey(),
            ])
                ->fillForm([
                    'name' => 'Updated Branch',
                ])
                ->call('save')
                ->assertRedirect(BranchResource::getUrl('index'));
        });

        it('sends success notification after update', function () {
            $branch = Branch::factory()->create();

            Livewire::test(EditBranch::class, [
                'record' => $branch->getRouteKey(),
            ])
                ->fillForm([
                    'name' => 'Updated Branch',
                ])
                ->call('save')
                ->assertNotified();
        });

        it('has delete action in header', function () {
            $branch = Branch::factory()->create();

            Livewire::test(EditBranch::class, [
                'record' => $branch->getRouteKey(),
            ])
                ->assertActionExists('delete');
        });

        it('can soft delete a branch from edit page', function () {
            $branch = Branch::factory()->create();

            Livewire::test(EditBranch::class, [
                'record' => $branch->getRouteKey(),
            ])
                ->callAction('delete');

            assertSoftDeleted('branches', [
                'id' => $branch->id,
            ]);
        });

        it('has restore action for soft deleted branches', function () {
            $branch = Branch::factory()->create();
            $branch->delete();

            Livewire::test(EditBranch::class, [
                'record' => $branch->getRouteKey(),
            ])
                ->assertActionExists('restore');
        });

        it('can restore a soft deleted branch', function () {
            $branch = Branch::factory()->create();
            $branch->delete();

            Livewire::test(EditBranch::class, [
                'record' => $branch->getRouteKey(),
            ])
                ->callAction('restore');

            assertDatabaseHas('branches', [
                'id' => $branch->id,
                'deleted_at' => null,
            ]);
        });

        it('has force delete action for soft deleted branches', function () {
            $branch = Branch::factory()->create();
            $branch->delete();

            Livewire::test(EditBranch::class, [
                'record' => $branch->getRouteKey(),
            ])
                ->assertActionExists('forceDelete');
        });

        it('can force delete a branch', function () {
            $branch = Branch::factory()->create();
            $branch->delete();
            $branchId = $branch->id;

            Livewire::test(EditBranch::class, [
                'record' => $branch->getRouteKey(),
            ])
                ->callAction('forceDelete');

            assertDatabaseMissing('branches', [
                'id' => $branchId,
            ]);
        });
    });

    describe('Table Actions', function () {
        it('can soft delete branch from table', function () {
            $branch = Branch::factory()->create();

            Livewire::test(ListBranches::class)
                ->callTableAction('delete', $branch);

            assertSoftDeleted('branches', [
                'id' => $branch->id,
            ]);
        });

        it('can bulk delete branches', function () {
            $branches = Branch::factory()->count(3)->create();

            Livewire::test(ListBranches::class)
                ->callTableBulkAction('delete', $branches);

            foreach ($branches as $branch) {
                assertSoftDeleted('branches', [
                    'id' => $branch->id,
                ]);
            }
        });

        it('can bulk restore branches', function () {
            $branches = Branch::factory()->count(3)->create();
            foreach ($branches as $branch) {
                $branch->delete();
            }

            Livewire::test(ListBranches::class)
                ->filterTable('trashed', 'only')
                ->callTableBulkAction('restore', $branches);

            foreach ($branches as $branch) {
                assertDatabaseHas('branches', [
                    'id' => $branch->id,
                    'deleted_at' => null,
                ]);
            }
        });

        it('can bulk force delete branches', function () {
            $branches = Branch::factory()->count(3)->create();
            foreach ($branches as $branch) {
                $branch->delete();
            }

            Livewire::test(ListBranches::class)
                ->filterTable('trashed', 'only')
                ->callTableBulkAction('forceDelete', $branches);

            foreach ($branches as $branch) {
                assertDatabaseMissing('branches', [
                    'id' => $branch->id,
                ]);
            }
        });
    });

    describe('Navigation and Permissions', function () {
        it('has correct navigation icon', function () {
            expect(BranchResource::getNavigationIcon())
                ->toBe('heroicon-o-building-office-2');
        });

        it('has correct model label', function () {
            expect(BranchResource::getModelLabel())
                ->toBe('Branch');
        });

        it('has correct plural model label', function () {
            expect(BranchResource::getPluralModelLabel())
                ->toBe('Branches');
        });

        it('includes soft deleted records in eloquent query', function () {
            $activeBranch = Branch::factory()->create();
            $deletedBranch = Branch::factory()->create();
            $deletedBranch->delete();

            $query = BranchResource::getEloquentQuery();

            expect($query->count())->toBe(2);
        });
    });
});
