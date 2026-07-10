<?php

namespace App\Domains\TaskList\Actions\DeleteTaskList;

use App\Domains\TaskList\Models\TaskListModel;

class DeleteTaskListCommand
{
    public function __construct(
        public readonly TaskListModel $taskList,
    ) {}
}
