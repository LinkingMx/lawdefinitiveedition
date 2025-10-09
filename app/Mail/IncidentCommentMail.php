<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Incident;
use App\Models\IncidentComment;
use Spatie\MailTemplates\TemplateMailable;

class IncidentCommentMail extends TemplateMailable
{
    public function __construct(
        public Incident $incident,
        public IncidentComment $comment,
        public $user
    ) {
        // Las propiedades públicas están disponibles como variables Mustache
    }
}
