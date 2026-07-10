<?php

namespace App\Domains\Task\Actions\UpdateTask;

use App\Domains\Task\Models\TaskModel;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateTaskHandler
{
    use AsAction;

    public function handle(UpdateTaskCommand $command): TaskModel
    {
        $command->task->update([
            'task_list_id' => $command->taskListId,
            'name'         => $command->name,
            'description'  => $command->description,
            'start_date'   => $command->startDate,
            'due_date'     => $command->dueDate,
            'priority'     => $command->priority->value,
            'status'       => $command->status?->value,
        ]);

        if ($command->tagIds !== null) {
            $command->task->tags()->sync($command->tagIds);
        }

        return $command->task->fresh();
    }
}
