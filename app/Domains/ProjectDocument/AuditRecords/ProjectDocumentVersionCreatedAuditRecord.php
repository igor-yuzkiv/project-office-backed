<?php

namespace App\Domains\ProjectDocument\AuditRecords;

use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Domains\ProjectDocument\Models\ProjectDocumentVersionModel;
use App\Libs\AuditTrail\Concerns\DescribesSubject;
use App\Libs\AuditTrail\Concerns\ResolvesActorName;
use App\Libs\AuditTrail\Contracts\AuditRecord;
use Illuminate\Database\Eloquent\Model;

/**
 * The subject is the document, not the version: a version has no page of its own, and the
 * document's activity is filtered by subject, so a version event has to sit under it to appear
 * there at all. The version is named in the sentence instead.
 */
class ProjectDocumentVersionCreatedAuditRecord implements AuditRecord
{
    use DescribesSubject, ResolvesActorName;

    public function __construct(
        private readonly ProjectDocumentModel $document,
        private readonly ProjectDocumentVersionModel $version,
    ) {}

    public function type(): string
    {
        return 'project_document_version.created';
    }

    public function title(): string
    {
        return "{$this->actorName()} created version {$this->version->version_number} of {$this->subjectName($this->document)}";
    }

    public function description(): ?string
    {
        return $this->version->label;
    }

    public function subject(): ?Model
    {
        return $this->document;
    }
}
