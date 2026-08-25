<?php

namespace App\Domains\ProjectDocument\AuditRecords;

use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Domains\ProjectDocument\Models\ProjectDocumentVersionModel;
use App\Libs\AuditTrail\Concerns\DescribesSubject;
use App\Libs\AuditTrail\Concerns\ListsChangedFields;
use App\Libs\AuditTrail\Concerns\ResolvesActorName;
use App\Libs\AuditTrail\Contracts\AuditRecord;
use Illuminate\Database\Eloquent\Model;

/** @see ProjectDocumentVersionCreatedAuditRecord for why the document is the subject. */
class ProjectDocumentVersionUpdatedAuditRecord implements AuditRecord
{
    use DescribesSubject, ListsChangedFields, ResolvesActorName;

    /**
     * Bounded to what a version write can change. A column missing from this map drops out of the
     * change set and can silence the event, so adding one to the handler means adding it here.
     */
    protected const FIELD_NAMES = [
        'content' => 'content',
        'label'   => 'label',
    ];

    /**
     * @param  string[]  $changedColumns
     */
    public function __construct(
        private readonly ProjectDocumentModel $document,
        private readonly ProjectDocumentVersionModel $version,
        private readonly array $changedColumns,
    ) {}

    public function type(): string
    {
        return 'project_document_version.updated';
    }

    public function title(): string
    {
        return "{$this->actorName()} updated version {$this->version->version_number} of {$this->subjectName($this->document)}";
    }

    public function description(): ?string
    {
        return $this->changedFieldsSentence($this->changedColumns);
    }

    public function subject(): ?Model
    {
        return $this->document;
    }
}
