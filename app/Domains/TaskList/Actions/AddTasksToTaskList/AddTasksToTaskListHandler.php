<?php

namespace App\Domains\TaskList\Actions\AddTasksToTaskList;

use App\Domains\Task\Models\TaskModel;
use Illuminate\Database\Eloquent\Collection;

class AddTasksToTaskListHandler
{
    /**
     * Adds only. Tasks already belonging to another list are left alone — the request layer
     * rejects them, and the query here refuses them a second time so the handler is safe on
     * its own.
     *
     * @return Collection<int, TaskModel>
     */
    public function handle(AddTasksToTaskListCommand $command): Collection
    {
        TaskModel::whereIn('id', $command->taskIds)
            ->where('project_id', $command->taskList->project_id)
            ->whereNull('task_list_id')
            ->update([
                'task_list_id' => $command->taskList->id,
                // A query-builder update skips the `updating` model event, so the audit column
                // HasAuditableColumns would have written is set here explicitly.
                'updated_by' => auth()->id(),
            ]);

        /** @var Collection<int, TaskModel> $tasks */
        $tasks = TaskModel::whereIn('id', $command->taskIds)
            ->where('task_list_id', $command->taskList->id)
            ->with(['createdBy', 'updatedBy', 'tags'])
            ->get();

        return $tasks;
    }
}
