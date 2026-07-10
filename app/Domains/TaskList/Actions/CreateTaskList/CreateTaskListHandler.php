<?php

namespace App\Domains\TaskList\Actions\CreateTaskList;

use App\Domains\TaskList\Models\TaskListModel;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateTaskListHandler
{
    use AsAction;

    public function handle(CreateTaskListCommand $command): TaskListModel
    {
        return TaskListModel::create([
            'project_id' => $command->projectId,
            'name'       => $command->name,
        ]);
    }
}
