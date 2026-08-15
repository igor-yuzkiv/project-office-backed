<?php

namespace App\Domains\ProjectDocument\AuditRecords;

use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Libs\AuditTrail\Concerns\DescribesSubject;
use App\Libs\AuditTrail\Concerns\ResolvesActorName;
use App\Libs\AuditTrail\Contracts\AuditRecord;
use Illuminate\Database\Eloquent\Model;

class ProjectDocumentUpdatedAuditRecord implements AuditRecord
{
    use DescribesSubject, ResolvesActorName;

    public function __construct(private readonly ProjectDocumentModel $document) {}

    public function type(): string
    {
        return 'project_document.updated';
    }

    public function title(): string
    {
        return "{$this->actorName()} updated {$this->subjectName($this->document)}";
    }

    public function description(): ?string
    {
        return null;
    }

    public function subject(): ?Model
    {
        return $this->document;
    }
}
