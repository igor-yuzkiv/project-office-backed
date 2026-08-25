<?php

namespace App\Domains\ProjectDocument\AuditRecords;

use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Libs\AuditTrail\Concerns\DescribesSubject;
use App\Libs\AuditTrail\Concerns\ResolvesActorName;
use App\Libs\AuditTrail\Contracts\AuditRecord;
use Illuminate\Database\Eloquent\Model;

/**
 * Unlike the other deletions in the feed this one still links somewhere: what disappeared is the
 * version, and the document it belonged to is still there to open.
 *
 * The version number is taken before the row goes, since afterwards there is nothing to read it
 * from.
 */
class ProjectDocumentVersionDeletedAuditRecord implements AuditRecord
{
    use DescribesSubject, ResolvesActorName;

    public function __construct(
        private readonly ProjectDocumentModel $document,
        private readonly int $versionNumber,
        private readonly ?string $label,
    ) {}

    public function type(): string
    {
        return 'project_document_version.deleted';
    }

    public function title(): string
    {
        return "{$this->actorName()} deleted version {$this->versionNumber} of {$this->subjectName($this->document)}";
    }

    public function description(): ?string
    {
        return $this->label;
    }

    public function subject(): ?Model
    {
        return $this->document;
    }
}
