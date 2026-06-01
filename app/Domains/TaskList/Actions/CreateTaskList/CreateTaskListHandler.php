<?php

namespace App\Domains\TaskList\Actions\CreateTaskList;

use App\Domains\TaskList\Models\TaskListModel;

class CreateTaskListHandler
{
    public function handle(CreateTaskListCommand $command): TaskListModel
    {
        return TaskListModel::create([
            'project_id' => $command->projectId,
            'name'       => $command->name,
        ]);
    }
}
