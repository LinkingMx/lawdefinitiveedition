<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Document;
use Spatie\MailTemplates\TemplateMailable;

class DocumentUploadedMail extends TemplateMailable
{
    public string $upload_date;

    public $uploader;

    public function __construct(
        public Document $document
    ) {
        $this->upload_date = $document->created_at->format('d/m/Y H:i');
        $this->uploader = $document->uploadedBy;
    }
}
