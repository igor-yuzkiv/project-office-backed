<?php

namespace App\Domains\Project\AuditRecords;

use App\Domains\Project\Models\ProjectModel;
use App\Libs\AuditTrail\Concerns\DescribesSubject;
use App\Libs\AuditTrail\Concerns\ListsChangedFields;
use App\Libs\AuditTrail\Concerns\ResolvesActorName;
use App\Libs\AuditTrail\Contracts\AuditRecord;
use Illuminate\Database\Eloquent\Model;

class ProjectUpdatedAuditRecord implements AuditRecord
{
    use DescribesSubject, ListsChangedFields, ResolvesActorName;

    /**
     * Bounded to the columns UpdateProjectHandler writes; `updated_at` and `updated_by` change on
     * every write (HasAuditableColumns).
     *
     * Adding a column to UpdateProjectHandler means adding it here too: a column missing from this
     * map is not merely unnamed, it drops out of the change set and can silence the event.
     */
    protected const FIELD_NAMES = [
        'name'        => 'name',
        'status'      => 'status',
        'description' => 'description',
        'start_date'  => 'start date',
        'end_date'    => 'end date',
    ];

    /**
     * @param  string[]  $changedColumns
     */
    public function __construct(
        private readonly ProjectModel $project,
        private readonly array $changedColumns,
    ) {}

    public function type(): string
    {
        return 'project.updated';
    }

    public function title(): string
    {
        return "{$this->actorName()} updated project {$this->subjectName($this->project)}";
    }

    public function description(): ?string
    {
        return $this->changedFieldsSentence($this->changedColumns);
    }

    public function subject(): ?Model
    {
        return $this->project;
    }
}
