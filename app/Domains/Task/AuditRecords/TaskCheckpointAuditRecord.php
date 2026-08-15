<?php

namespace App\Domains\Task\AuditRecords;

use App\Domains\Task\Models\TaskModel;
use App\Libs\AuditTrail\Concerns\ResolvesActorName;
use App\Libs\AuditTrail\Contracts\AuditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Built from the command's own fields, not from the rendered comment body: the '# Checkpoint: …'
 * markdown is a presentation detail of the comment, not a source of data.
 */
class TaskCheckpointAuditRecord implements AuditRecord
{
    use ResolvesActorName;

    private const EXCERPT_LIMIT = 500;

    public function __construct(
        private readonly TaskModel $task,
        private readonly string $subject,
        private readonly string $comment,
    ) {}

    public function type(): string
    {
        return 'task.checkpoint';
    }

    public function title(): string
    {
        return "{$this->actorName()} recorded a checkpoint on {$this->task->key}";
    }

    public function description(): ?string
    {
        return $this->subject.' — '.Str::limit($this->comment, self::EXCERPT_LIMIT, '…');
    }

    public function subject(): ?Model
    {
        return $this->task;
    }
}
