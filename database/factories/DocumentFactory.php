<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $mimeTypes = [
            'application/pdf',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'image/jpeg',
            'image/png',
        ];

        $extensions = [
            'application/pdf' => 'pdf',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
        ];

        $mimeType = $this->faker->randomElement($mimeTypes);
        $extension = $extensions[$mimeType];
        $filename = $this->faker->words(3, true).'.'.$extension;

        return [
            'document_type_id' => DocumentType::factory(),
            'branch_id' => Branch::factory(),
            'description' => $this->faker->boolean(70) ? $this->faker->sentence(10) : null,
            'expires_at' => $this->faker->boolean(50) ? $this->faker->dateTimeBetween('now', '+1 year') : null,
            'file_path' => 'documents/'.$this->faker->uuid().'.'.$extension,
            'original_filename' => $filename,
            'file_size' => $this->faker->numberBetween(100000, 10485760), // 100KB - 10MB
            'mime_type' => $mimeType,
            'uploaded_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the document has expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => $this->faker->dateTimeBetween('-1 year', '-1 day'),
        ]);
    }

    /**
     * Indicate that the document has no expiration date.
     */
    public function noExpiration(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => null,
        ]);
    }

    /**
     * Indicate that the document has a description.
     */
    public function withDescription(): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => $this->faker->paragraph(3),
        ]);
    }

    /**
     * Indicate that the document has no description.
     */
    public function withoutDescription(): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => null,
        ]);
    }
}
