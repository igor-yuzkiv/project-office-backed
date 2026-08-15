<?php

namespace App\Domains\ProjectDocument\AuditRecords;

use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Libs\AuditTrail\Concerns\DescribesSubject;
use App\Libs\AuditTrail\Concerns\ResolvesActorName;
use App\Libs\AuditTrail\Contracts\AuditRecord;
use Illuminate\Database\Eloquent\Model;

class ProjectDocumentCreatedAuditRecord implements AuditRecord
{
    use DescribesSubject, ResolvesActorName;

    public function __construct(
        private readonly ProjectDocumentModel $document,
        private readonly string $projectName,
    ) {}

    public function type(): string
    {
        return 'project_document.created';
    }

    public function title(): string
    {
        return "{$this->actorName()} created {$this->subjectName($this->document)}";
    }

    public function description(): ?string
    {
        return $this->projectName;
    }

    public function subject(): ?Model
    {
        return $this->document;
    }
}
