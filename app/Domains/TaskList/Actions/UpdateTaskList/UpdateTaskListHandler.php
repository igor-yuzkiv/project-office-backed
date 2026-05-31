<?php

namespace App\Domains\TaskList\Actions\UpdateTaskList;

use App\Domains\TaskList\Models\TaskListModel;

class UpdateTaskListHandler
{
    public function handle(UpdateTaskListCommand $command): TaskListModel
    {
        $data = array_filter(
            ['name' => $command->name],
            fn ($value) => $value !== null
        );

        $command->taskList->update($data);

        return $command->taskList->fresh();
    }
}
