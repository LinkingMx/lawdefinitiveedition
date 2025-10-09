<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Incident;
use Spatie\MailTemplates\TemplateMailable;

class IncidentCreatedMail extends TemplateMailable
{
    public function __construct(
        public Incident $incident
    ) {
        // Las propiedades públicas están disponibles como variables Mustache
    }
}
