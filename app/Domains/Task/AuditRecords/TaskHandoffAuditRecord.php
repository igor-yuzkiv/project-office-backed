<?php

namespace App\Domains\Task\AuditRecords;

use App\Domains\Task\Models\TaskModel;
use App\Libs\AuditTrail\Concerns\ResolvesActorName;
use App\Libs\AuditTrail\Contracts\AuditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TaskHandoffAuditRecord implements AuditRecord
{
    use ResolvesActorName;

    private const EXCERPT_LIMIT = 500;

    public function __construct(
        private readonly TaskModel $task,
        private readonly string $resolution,
    ) {}

    public function type(): string
    {
        return 'task.handoff';
    }

    public function title(): string
    {
        return "{$this->actorName()} handed off {$this->task->key} for testing";
    }

    public function description(): ?string
    {
        return Str::limit($this->resolution, self::EXCERPT_LIMIT, '…');
    }

    public function subject(): ?Model
    {
        return $this->task;
    }
}
