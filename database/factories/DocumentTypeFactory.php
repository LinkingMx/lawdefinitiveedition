<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DocumentType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DocumentType>
 */
class DocumentTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\DocumentType>
     */
    protected $model = DocumentType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $documentTypes = [
            'Contract',
            'Invoice',
            'Agreement',
            'Memorandum',
            'Letter',
            'Report',
            'Proposal',
            'Certificate',
            'Affidavit',
            'Petition',
            'Complaint',
            'Motion',
            'Brief',
            'Pleading',
            'Deposition',
            'Subpoena',
            'Warrant',
            'Order',
            'Judgment',
            'Decree',
        ];

        return [
            'name' => fake()->unique()->randomElement($documentTypes),
            'description' => fake()->sentence(12),
        ];
    }

    /**
     * Indicate that the document type should not have a description.
     */
    public function withoutDescription(): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => null,
        ]);
    }

    /**
     * Indicate that the document type is soft deleted.
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
        ]);
    }
}
