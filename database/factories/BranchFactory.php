<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Branch>
 */
class BranchFactory extends Factory
{
    protected $model = Branch::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company().' - '.fake()->city().' Branch',
            'address' => fake()->address(),
            'contact_name' => fake()->name(),
            'contact_email' => fake()->companyEmail(),
            'contact_phone' => fake()->phoneNumber(),
        ];
    }

    /**
     * Indicate that the branch has minimal information (only name).
     */
    public function minimal(): static
    {
        return $this->state(fn (array $attributes) => [
            'address' => null,
            'contact_name' => null,
            'contact_email' => null,
            'contact_phone' => null,
        ]);
    }

    /**
     * Indicate that the branch is soft deleted.
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
        ]);
    }

    /**
     * Indicate that the branch has no contact information.
     */
    public function noContact(): static
    {
        return $this->state(fn (array $attributes) => [
            'contact_name' => null,
            'contact_email' => null,
            'contact_phone' => null,
        ]);
    }
}
