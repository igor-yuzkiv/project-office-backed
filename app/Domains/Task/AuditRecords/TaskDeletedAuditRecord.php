<?php

namespace App\Domains\Task\AuditRecords;

use App\Domains\Task\Models\TaskModel;
use App\Libs\AuditTrail\Concerns\ResolvesActorName;
use App\Libs\AuditTrail\Contracts\AuditRecord;
use Illuminate\Database\Eloquent\Model;

class TaskDeletedAuditRecord implements AuditRecord
{
    use ResolvesActorName;

    /**
     * Built before the task row is deleted; the model keeps its attributes afterwards, and
     * subject_id does not depend on the row still existing.
     */
    public function __construct(private readonly TaskModel $task) {}

    public function type(): string
    {
        return 'task.deleted';
    }

    public function title(): string
    {
        return "{$this->actorName()} deleted {$this->task->key}";
    }

    public function description(): ?string
    {
        return $this->task->description;
    }

    public function subject(): ?Model
    {
        return $this->task;
    }
}
