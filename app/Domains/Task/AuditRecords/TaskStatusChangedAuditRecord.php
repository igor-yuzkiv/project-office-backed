<?php

namespace App\Domains\Task\AuditRecords;

use App\Domains\Task\Enums\TaskStatus;
use App\Domains\Task\Models\TaskModel;
use App\Libs\AuditTrail\Concerns\ResolvesActorName;
use App\Libs\AuditTrail\Contracts\AuditRecord;
use Illuminate\Database\Eloquent\Model;

class TaskStatusChangedAuditRecord implements AuditRecord
{
    use ResolvesActorName;

    public function __construct(
        private readonly TaskModel $task,
        private readonly TaskStatus $previousStatus,
        private readonly TaskStatus $currentStatus,
    ) {}

    public function type(): string
    {
        return 'task.status_changed';
    }

    public function title(): string
    {
        return "{$this->actorName()} moved {$this->task->key} to {$this->currentStatus->label()}";
    }

    public function description(): ?string
    {
        return "{$this->previousStatus->label()} → {$this->currentStatus->label()}";
    }

    public function subject(): ?Model
    {
        return $this->task;
    }
}
