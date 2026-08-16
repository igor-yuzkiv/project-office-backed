<?php

namespace App\Domains\TaskList\Actions\UpdateTaskList;

use App\Domains\TaskList\AuditRecords\TaskListUpdatedAuditRecord;
use App\Domains\TaskList\Models\TaskListModel;
use App\Libs\AuditTrail\Facades\AuditTrail;

class UpdateTaskListHandler
{
    public function handle(UpdateTaskListCommand $command): TaskListModel
    {
        $command->taskList->update([
            'name'        => $command->name,
            'status'      => $command->status->value,
            'description' => $command->description,
        ]);

        $changed = TaskListUpdatedAuditRecord::reportableColumns(array_keys($command->taskList->getChanges()));

        if ($changed !== []) {
            AuditTrail::capture(new TaskListUpdatedAuditRecord($command->taskList, $changed));
        }

        if ($command->tagIds !== null) {
            $command->taskList->tags()->sync($command->tagIds);
        }

        return $command->taskList->fresh();
    }
}
