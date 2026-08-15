<?php

namespace App\Domains\TaskList\AuditRecords;

use App\Domains\TaskList\Models\TaskListModel;
use App\Libs\AuditTrail\Concerns\DescribesSubject;
use App\Libs\AuditTrail\Concerns\ResolvesActorName;
use App\Libs\AuditTrail\Contracts\AuditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * One event per call: the keys are listed in the description, so the feed shows what moved
 * without a line per task.
 */
class TasksAddedToTaskListAuditRecord implements AuditRecord
{
    use DescribesSubject, ResolvesActorName;

    /**
     * @param  string[]  $taskKeys
     */
    public function __construct(
        private readonly TaskListModel $taskList,
        private readonly array $taskKeys,
    ) {}

    public function type(): string
    {
        return 'task_list.tasks_added';
    }

    public function title(): string
    {
        $count = count($this->taskKeys);

        return "{$this->actorName()} added {$count} ".Str::plural('task', $count).' to '.$this->subjectName($this->taskList);
    }

    public function description(): ?string
    {
        return $this->taskKeys === [] ? null : implode(', ', $this->taskKeys);
    }

    public function subject(): ?Model
    {
        return $this->taskList;
    }
}
