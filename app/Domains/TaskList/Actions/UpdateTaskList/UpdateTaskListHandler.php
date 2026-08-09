<?php

namespace App\Domains\TaskList\Actions\UpdateTaskList;

use App\Domains\TaskList\Models\TaskListModel;

class UpdateTaskListHandler
{
    public function handle(UpdateTaskListCommand $command): TaskListModel
    {
        $command->taskList->update([
            'name'        => $command->name,
            'status'      => $command->status->value,
            'description' => $command->description,
        ]);

        if ($command->tagIds !== null) {
            $command->taskList->tags()->sync($command->tagIds);
        }

        return $command->taskList->fresh();
    }
}
