<?php

namespace App\Domains\TaskList\Actions\DeleteTaskList;

use App\Domains\TaskList\Models\TaskListModel;

class DeleteTaskListHandler
{
    public function handle(TaskListModel $taskList): void
    {
        $taskList->delete();
    }
}
