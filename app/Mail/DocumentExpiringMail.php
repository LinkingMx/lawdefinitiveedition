<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Document;
use Spatie\MailTemplates\TemplateMailable;

class DocumentExpiringMail extends TemplateMailable
{
    public int $days_until_expiry;

    public function __construct(
        public Document $document
    ) {
        $this->days_until_expiry = (int) now()->diffInDays($document->expires_at);
    }
}
