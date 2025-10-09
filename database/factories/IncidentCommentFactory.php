<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Incident;
use App\Models\IncidentComment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\IncidentComment>
 */
class IncidentCommentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = IncidentComment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'incident_id' => Incident::factory(),
            'user_id' => User::factory(),
            'comment' => fake()->paragraph(),
        ];
    }

    /**
     * Indicate that the comment is short.
     */
    public function short(): static
    {
        return $this->state(fn (array $attributes) => [
            'comment' => fake()->sentence(),
        ]);
    }

    /**
     * Indicate that the comment is long.
     */
    public function long(): static
    {
        return $this->state(fn (array $attributes) => [
            'comment' => fake()->paragraphs(5, true),
        ]);
    }
}
