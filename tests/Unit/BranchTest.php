<?php

declare(strict_types=1);

use App\Models\Branch;

describe('Branch Model', function () {
    it('can be created with factory', function () {
        $branch = Branch::factory()->create();

        expect($branch)->toBeInstanceOf(Branch::class)
            ->and($branch->id)->not->toBeNull()
            ->and($branch->name)->not->toBeNull();
    });

    it('has fillable attributes', function () {
        $fillable = [
            'name',
            'address',
            'contact_name',
            'contact_email',
            'contact_phone',
        ];

        $branch = new Branch;

        expect($branch->getFillable())->toBe($fillable);
    });

    it('casts dates correctly', function () {
        $branch = Branch::factory()->create();

        expect($branch->created_at)->toBeInstanceOf(DateTime::class)
            ->and($branch->updated_at)->toBeInstanceOf(DateTime::class);
    });

    it('uses soft deletes', function () {
        $branch = Branch::factory()->create();
        $branchId = $branch->id;

        $branch->delete();

        // Should be soft deleted
        expect($branch->deleted_at)->not->toBeNull()
            ->and(Branch::find($branchId))->toBeNull()
            ->and(Branch::withTrashed()->find($branchId))->not->toBeNull();
    });

    it('can be restored after soft delete', function () {
        $branch = Branch::factory()->create();
        $branchId = $branch->id;

        $branch->delete();
        $branch->restore();

        expect($branch->deleted_at)->toBeNull()
            ->and(Branch::find($branchId))->not->toBeNull();
    });

    it('can be force deleted', function () {
        $branch = Branch::factory()->create();
        $branchId = $branch->id;

        $branch->forceDelete();

        expect(Branch::withTrashed()->find($branchId))->toBeNull();
    });

    it('can create branch with only required fields', function () {
        $branch = Branch::factory()->minimal()->create([
            'name' => 'Test Branch',
        ]);

        expect($branch->name)->toBe('Test Branch')
            ->and($branch->address)->toBeNull()
            ->and($branch->contact_name)->toBeNull()
            ->and($branch->contact_email)->toBeNull()
            ->and($branch->contact_phone)->toBeNull();
    });

    it('can create branch with all fields', function () {
        $data = [
            'name' => 'Main Office',
            'address' => '123 Main St, New York, NY 10001',
            'contact_name' => 'John Doe',
            'contact_email' => 'john@example.com',
            'contact_phone' => '+1-555-1234',
        ];

        $branch = Branch::factory()->create($data);

        expect($branch->name)->toBe($data['name'])
            ->and($branch->address)->toBe($data['address'])
            ->and($branch->contact_name)->toBe($data['contact_name'])
            ->and($branch->contact_email)->toBe($data['contact_email'])
            ->and($branch->contact_phone)->toBe($data['contact_phone']);
    });

    it('updates timestamps automatically', function () {
        $branch = Branch::factory()->create();
        $originalUpdatedAt = $branch->updated_at;

        sleep(1); // Ensure time difference

        $branch->update(['name' => 'Updated Branch']);

        expect($branch->updated_at)->not->toBe($originalUpdatedAt)
            ->and($branch->updated_at->greaterThan($originalUpdatedAt))->toBeTrue();
    });

    it('uses HasFactory trait', function () {
        expect(Branch::class)
            ->toHaveMethod('factory');
    });

    it('uses SoftDeletes trait', function () {
        expect(Branch::class)
            ->toHaveMethod('restore')
            ->toHaveMethod('forceDelete')
            ->toHaveMethod('trashed');
    });
});
