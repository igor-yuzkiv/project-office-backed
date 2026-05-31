<?php

namespace App\Domains\Task\Actions\UpdateTask;

use App\Domains\Task\Models\TaskModel;

class UpdateTaskHandler
{
    public function handle(UpdateTaskCommand $command): TaskModel
    {
        $data = array_filter(
            [
                'task_list_id' => $command->taskListId,
                'name'         => $command->name,
                'description'  => $command->description,
                'priority'     => $command->priority?->value,
                'status'       => $command->status?->value,
            ],
            fn ($value) => $value !== null
        );

        $command->task->update($data);

        return $command->task->fresh();
    }
}
