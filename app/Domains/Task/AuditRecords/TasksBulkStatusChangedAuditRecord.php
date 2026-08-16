<?php

namespace App\Domains\Task\AuditRecords;

use App\Domains\Task\Enums\TaskStatus;
use App\Libs\AuditTrail\Concerns\ResolvesActorName;
use App\Libs\AuditTrail\Contracts\AuditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * One event for the whole operation, without a subject: a bulk change belongs to no single task.
 */
class TasksBulkStatusChangedAuditRecord implements AuditRecord
{
    use ResolvesActorName;

    /**
     * @param  string[]  $taskKeys
     */
    public function __construct(
        private readonly array $taskKeys,
        private readonly TaskStatus $status,
    ) {}

    public function type(): string
    {
        return 'task.bulk_status_changed';
    }

    public function title(): string
    {
        $count = count($this->taskKeys);

        return "{$this->actorName()} moved {$count} ".Str::plural('task', $count)." to {$this->status->label()}";
    }

    public function description(): ?string
    {
        return $this->taskKeys === [] ? null : implode(', ', $this->taskKeys);
    }

    public function subject(): ?Model
    {
        return null;
    }
}
