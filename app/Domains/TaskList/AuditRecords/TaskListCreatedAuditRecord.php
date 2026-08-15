<?php

namespace App\Domains\TaskList\AuditRecords;

use App\Domains\TaskList\Models\TaskListModel;
use App\Libs\AuditTrail\Concerns\DescribesSubject;
use App\Libs\AuditTrail\Concerns\ResolvesActorName;
use App\Libs\AuditTrail\Contracts\AuditRecord;
use Illuminate\Database\Eloquent\Model;

class TaskListCreatedAuditRecord implements AuditRecord
{
    use DescribesSubject, ResolvesActorName;

    public function __construct(private readonly TaskListModel $taskList) {}

    public function type(): string
    {
        return 'task_list.created';
    }

    public function title(): string
    {
        return "{$this->actorName()} created list {$this->subjectName($this->taskList)}";
    }

    public function description(): ?string
    {
        return $this->taskList->description;
    }

    public function subject(): ?Model
    {
        return $this->taskList;
    }
}
