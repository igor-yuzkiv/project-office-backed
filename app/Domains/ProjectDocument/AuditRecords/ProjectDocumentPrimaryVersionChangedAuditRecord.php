<?php

namespace App\Domains\ProjectDocument\AuditRecords;

use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Domains\ProjectDocument\Models\ProjectDocumentVersionModel;
use App\Libs\AuditTrail\Concerns\DescribesSubject;
use App\Libs\AuditTrail\Concerns\ResolvesActorName;
use App\Libs\AuditTrail\Contracts\AuditRecord;
use Illuminate\Database\Eloquent\Model;

class ProjectDocumentPrimaryVersionChangedAuditRecord implements AuditRecord
{
    use DescribesSubject, ResolvesActorName;

    public function __construct(
        private readonly ProjectDocumentModel $document,
        /** Null is the document going back to following whichever version is newest. */
        private readonly ?ProjectDocumentVersionModel $version,
    ) {}

    public function type(): string
    {
        return 'project_document_version.primary_changed';
    }

    public function title(): string
    {
        $subject = $this->subjectName($this->document);

        return $this->version === null
            ? "{$this->actorName()} made {$subject} follow its latest version"
            : "{$this->actorName()} made version {$this->version->version_number} primary for {$subject}";
    }

    public function description(): ?string
    {
        return $this->version?->label;
    }

    public function subject(): ?Model
    {
        return $this->document;
    }
}
