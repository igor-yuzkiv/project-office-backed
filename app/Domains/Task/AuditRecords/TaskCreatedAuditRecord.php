<?php

namespace App\Domains\Task\AuditRecords;

use App\Domains\Task\Models\TaskModel;
use App\Libs\AuditTrail\Concerns\ResolvesActorName;
use App\Libs\AuditTrail\Contracts\AuditRecord;
use Illuminate\Database\Eloquent\Model;

class TaskCreatedAuditRecord implements AuditRecord
{
    use ResolvesActorName;

    public function __construct(private readonly TaskModel $task) {}

    public function type(): string
    {
        return 'task.created';
    }

    public function title(): string
    {
        return "{$this->actorName()} created {$this->task->key}";
    }

    public function description(): ?string
    {
        return $this->task->name;
    }

    public function subject(): ?Model
    {
        return $this->task;
    }
}
