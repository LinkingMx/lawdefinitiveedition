<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\MailTemplate;
use Illuminate\Database\Seeder;

class MailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        MailTemplate::create([
            'name' => 'Notificación de Incidente Creado',
            'category' => 'incidents',
            'mailable' => 'App\\Mail\\IncidentCreatedMail',
            'subject' => 'Nuevo Incidente: {{ incident.title }}',
            'html_template' => '<h2>Nuevo Incidente Reportado</h2>
<p>Hola {{ reporter.name }},</p>
<p>Se ha creado un nuevo incidente en {{ branch.name }}:</p>
<ul>
    <li><strong>Título:</strong> {{ incident.title }}</li>
    <li><strong>Prioridad:</strong> {{ incident.priority }}</li>
    <li><strong>Descripción:</strong> {{ incident.description }}</li>
</ul>
<p>Gracias.</p>',
            'text_template' => 'Nuevo Incidente: {{ incident.title }}

Prioridad: {{ incident.priority }}
Descripción: {{ incident.description }}
Sucursal: {{ branch.name }}

Reportado por: {{ reporter.name }}',
            'available_variables' => [
                'incident.title' => 'Título del incidente',
                'incident.priority' => 'Prioridad',
                'incident.description' => 'Descripción',
                'reporter.name' => 'Nombre del reportador',
                'branch.name' => 'Sucursal',
            ],
        ]);

        MailTemplate::create([
            'name' => 'Notificación de Comentario en Incidente',
            'category' => 'incidents',
            'mailable' => 'App\\Mail\\IncidentCommentMail',
            'subject' => 'Nuevo comentario en: {{ incident.title }}',
            'html_template' => '<h2>Nuevo Comentario en Incidente</h2>
<p>Se ha agregado un nuevo comentario al incidente: <strong>{{ incident.title }}</strong></p>
<p><strong>Comentario de {{ user.name }}:</strong></p>
<blockquote style="background: #f5f5f5; padding: 15px; border-left: 4px solid #2563eb;">
    {{ comment.comment }}
</blockquote>
<p><em>Fecha: {{ comment.created_at }}</em></p>',
            'text_template' => 'Nuevo comentario en incidente: {{ incident.title }}

Comentario de {{ user.name }}:
{{ comment.comment }}

Fecha: {{ comment.created_at }}',
            'available_variables' => [
                'incident.title' => 'Título del incidente',
                'comment.comment' => 'Contenido del comentario',
                'user.name' => 'Nombre del comentarista',
                'comment.created_at' => 'Fecha del comentario',
            ],
        ]);

        MailTemplate::create([
            'name' => 'Alerta de Documento Próximo a Vencer',
            'category' => 'documents',
            'mailable' => 'App\\Mail\\DocumentExpiringMail',
            'subject' => 'Documento próximo a vencer: {{ document.original_filename }}',
            'html_template' => '<h2>⚠️ Aviso: Documento Próximo a Vencer</h2>
<p>El siguiente documento vencerá en <strong style="color: #dc2626;">{{ days_until_expiry }} días</strong>:</p>
<ul>
    <li><strong>Documento:</strong> {{ document.original_filename }}</li>
    <li><strong>Fecha de vencimiento:</strong> {{ document.expires_at }}</li>
    <li><strong>Sucursal:</strong> {{ branch.name }}</li>
</ul>
<p style="background: #fef3c7; padding: 15px; border-left: 4px solid #f59e0b;">
    Por favor, toma las acciones necesarias antes de la fecha de vencimiento.
</p>',
            'text_template' => 'AVISO: Documento Próximo a Vencer

El siguiente documento vencerá en {{ days_until_expiry }} días:

- Documento: {{ document.original_filename }}
- Fecha de vencimiento: {{ document.expires_at }}
- Sucursal: {{ branch.name }}

Por favor, toma las acciones necesarias.',
            'available_variables' => [
                'document.original_filename' => 'Nombre del documento',
                'document.expires_at' => 'Fecha de expiración',
                'days_until_expiry' => 'Días hasta vencer',
                'branch.name' => 'Sucursal',
            ],
        ]);

        MailTemplate::create([
            'name' => 'Notificación de Documento Cargado',
            'category' => 'documents',
            'mailable' => 'App\\Mail\\DocumentUploadedMail',
            'subject' => 'Nuevo documento cargado: {{ document.original_filename }}',
            'html_template' => '<h2>✅ Documento Cargado Exitosamente</h2>
<p>Se ha cargado un nuevo documento:</p>
<ul>
    <li><strong>Documento:</strong> {{ document.original_filename }}</li>
    <li><strong>Cargado por:</strong> {{ uploader.name }}</li>
    <li><strong>Sucursal:</strong> {{ branch.name }}</li>
    <li><strong>Fecha:</strong> {{ upload_date }}</li>
</ul>
<p style="color: #059669;">El documento está disponible en el sistema.</p>',
            'text_template' => 'Documento Cargado Exitosamente

Documento: {{ document.original_filename }}
Cargado por: {{ uploader.name }}
Sucursal: {{ branch.name }}
Fecha: {{ upload_date }}

El documento está disponible en el sistema.',
            'available_variables' => [
                'document.original_filename' => 'Nombre del documento',
                'uploader.name' => 'Usuario que subió',
                'branch.name' => 'Sucursal',
                'upload_date' => 'Fecha de carga',
            ],
        ]);
    }
}
