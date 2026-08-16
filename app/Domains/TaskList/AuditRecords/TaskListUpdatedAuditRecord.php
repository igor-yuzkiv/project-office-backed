<?php

namespace App\Domains\TaskList\AuditRecords;

use App\Domains\TaskList\Models\TaskListModel;
use App\Libs\AuditTrail\Concerns\DescribesSubject;
use App\Libs\AuditTrail\Concerns\ListsChangedFields;
use App\Libs\AuditTrail\Concerns\ResolvesActorName;
use App\Libs\AuditTrail\Contracts\AuditRecord;
use Illuminate\Database\Eloquent\Model;

class TaskListUpdatedAuditRecord implements AuditRecord
{
    use DescribesSubject, ListsChangedFields, ResolvesActorName;

    /**
     * Bounded to the columns UpdateTaskListHandler writes; `updated_at` and `updated_by` change on
     * every write (HasAuditableColumns).
     *
     * Adding a column to UpdateTaskListHandler means adding it here too: a column missing from this
     * map is not merely unnamed, it drops out of the change set and can silence the event.
     */
    protected const FIELD_NAMES = [
        'name'        => 'name',
        'status'      => 'status',
        'description' => 'description',
    ];

    /**
     * @param  string[]  $changedColumns
     */
    public function __construct(
        private readonly TaskListModel $taskList,
        private readonly array $changedColumns,
    ) {}

    public function type(): string
    {
        return 'task_list.updated';
    }

    public function title(): string
    {
        return "{$this->actorName()} updated list {$this->subjectName($this->taskList)}";
    }

    public function description(): ?string
    {
        return $this->changedFieldsSentence($this->changedColumns);
    }

    public function subject(): ?Model
    {
        return $this->taskList;
    }
}
