<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MailTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MailTemplate>
 */
class MailTemplateFactory extends Factory
{
    protected $model = MailTemplate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $mailables = [
            'App\\Mail\\IncidentCreatedMail' => [
                'category' => 'incidents',
                'name' => 'Incidente Creado',
                'subject' => 'Nuevo Incidente: {{ incident.title }}',
                'variables' => [
                    'incident.title' => 'Título del incidente',
                    'incident.priority' => 'Prioridad',
                    'incident.description' => 'Descripción',
                    'reporter.name' => 'Nombre del reportador',
                    'branch.name' => 'Sucursal',
                ],
            ],
            'App\\Mail\\IncidentCommentMail' => [
                'category' => 'incidents',
                'name' => 'Comentario en Incidente',
                'subject' => 'Nuevo comentario en: {{ incident.title }}',
                'variables' => [
                    'incident.title' => 'Título del incidente',
                    'comment.comment' => 'Contenido del comentario',
                    'user.name' => 'Nombre del comentarista',
                    'comment.created_at' => 'Fecha del comentario',
                ],
            ],
            'App\\Mail\\DocumentExpiringMail' => [
                'category' => 'documents',
                'name' => 'Documento Próximo a Vencer',
                'subject' => 'El documento {{ document.original_filename }} vence pronto',
                'variables' => [
                    'document.original_filename' => 'Nombre del documento',
                    'document.expires_at' => 'Fecha de expiración',
                    'days_until_expiry' => 'Días hasta vencer',
                    'branch.name' => 'Sucursal',
                ],
            ],
            'App\\Mail\\DocumentUploadedMail' => [
                'category' => 'documents',
                'name' => 'Documento Cargado',
                'subject' => 'Nuevo documento cargado: {{ document.original_filename }}',
                'variables' => [
                    'document.original_filename' => 'Nombre del documento',
                    'uploader.name' => 'Usuario que subió',
                    'branch.name' => 'Sucursal',
                    'upload_date' => 'Fecha de carga',
                ],
            ],
        ];

        $mailable = fake()->randomElement(array_keys($mailables));
        $config = $mailables[$mailable];

        return [
            'name' => $config['name'].' - '.fake()->unique()->word(),
            'category' => $config['category'],
            'mailable' => $mailable,
            'subject' => $config['subject'],
            'html_template' => '<p>Hola,</p><p>'.fake()->paragraph().'</p><p>Saludos,<br>El equipo</p>',
            'text_template' => fake()->optional()->paragraph(),
            'available_variables' => $config['variables'],
        ];
    }

    /**
     * Indicate that the mail template is for incident created.
     */
    public function incidentCreated(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Incidente Creado - '.fake()->word(),
            'category' => 'incidents',
            'mailable' => 'App\\Mail\\IncidentCreatedMail',
            'subject' => 'Nuevo Incidente: {{ incident.title }}',
            'available_variables' => [
                'incident.title' => 'Título del incidente',
                'incident.priority' => 'Prioridad',
                'incident.description' => 'Descripción',
                'reporter.name' => 'Nombre del reportador',
                'branch.name' => 'Sucursal',
            ],
        ]);
    }

    /**
     * Indicate that the mail template is for incident comment.
     */
    public function incidentComment(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Comentario en Incidente - '.fake()->word(),
            'category' => 'incidents',
            'mailable' => 'App\\Mail\\IncidentCommentMail',
            'subject' => 'Nuevo comentario en: {{ incident.title }}',
            'available_variables' => [
                'incident.title' => 'Título del incidente',
                'comment.comment' => 'Contenido del comentario',
                'user.name' => 'Nombre del comentarista',
                'comment.created_at' => 'Fecha del comentario',
            ],
        ]);
    }

    /**
     * Indicate that the mail template is for document expiring.
     */
    public function documentExpiring(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Documento Próximo a Vencer - '.fake()->word(),
            'category' => 'documents',
            'mailable' => 'App\\Mail\\DocumentExpiringMail',
            'subject' => 'El documento {{ document.original_filename }} vence pronto',
            'available_variables' => [
                'document.original_filename' => 'Nombre del documento',
                'document.expires_at' => 'Fecha de expiración',
                'days_until_expiry' => 'Días hasta vencer',
                'branch.name' => 'Sucursal',
            ],
        ]);
    }

    /**
     * Indicate that the mail template is for document uploaded.
     */
    public function documentUploaded(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Documento Cargado - '.fake()->word(),
            'category' => 'documents',
            'mailable' => 'App\\Mail\\DocumentUploadedMail',
            'subject' => 'Nuevo documento cargado: {{ document.original_filename }}',
            'available_variables' => [
                'document.original_filename' => 'Nombre del documento',
                'uploader.name' => 'Usuario que subió',
                'branch.name' => 'Sucursal',
                'upload_date' => 'Fecha de carga',
            ],
        ]);
    }
}
