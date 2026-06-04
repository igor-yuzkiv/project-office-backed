<?php

namespace App\Domains\Task\Actions\UpdateTask;

use App\Domains\Task\Models\TaskModel;

class UpdateTaskHandler
{
    public function handle(UpdateTaskCommand $command): TaskModel
    {
        $command->task->update([
            'task_list_id' => $command->taskListId,
            'name'         => $command->name,
            'description'  => $command->description,
            'priority'     => $command->priority?->value,
            'status'       => $command->status?->value,
        ]);

        return $command->task->fresh();
    }
}
