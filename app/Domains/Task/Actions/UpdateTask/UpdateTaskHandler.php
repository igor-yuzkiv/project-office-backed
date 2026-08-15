<?php

namespace App\Domains\Task\Actions\UpdateTask;

use App\Domains\Task\AuditRecords\TaskStatusChangedAuditRecord;
use App\Domains\Task\AuditRecords\TaskUpdatedAuditRecord;
use App\Domains\Task\Enums\TaskStatus;
use App\Domains\Task\Models\TaskModel;
use App\Libs\AuditTrail\Facades\AuditTrail;

class UpdateTaskHandler
{
    public function handle(UpdateTaskCommand $command): TaskModel
    {
        $previousStatus = $command->task->status;

        $command->task->update([
            'task_list_id' => $command->taskListId,
            'name'         => $command->name,
            'description'  => $command->description,
            'start_date'   => $command->startDate,
            'due_date'     => $command->dueDate,
            'priority'     => $command->priority->value,
            'status'       => $command->status?->value,
        ]);

        $this->recordAudit($command->task, $previousStatus);

        if ($command->tagIds !== null) {
            $command->task->tags()->sync($command->tagIds);
        }

        return $command->task->fresh();
    }

    /**
     * A status change and an edit of other fields are different facts, so one update can produce
     * both events. The changed columns come from getChanges() — the handler always writes every
     * field, so only Eloquent knows what actually moved.
     */
    private function recordAudit(TaskModel $task, TaskStatus $previousStatus): void
    {
        $changed = array_keys($task->getChanges());

        if (in_array('status', $changed, true)) {
            AuditTrail::capture(new TaskStatusChangedAuditRecord($task, $previousStatus, $task->status));
        }

        $reportable = TaskUpdatedAuditRecord::reportableColumns($changed);

        if ($reportable !== []) {
            AuditTrail::capture(new TaskUpdatedAuditRecord($task, $reportable));
        }
    }
}
