<?php

namespace App\Domains\TaskList\Actions\AddTasksToTaskList;

use App\Domains\TaskList\Models\TaskListModel;

class AddTasksToTaskListCommand
{
    /**
     * @param  string[]  $taskIds
     */
    public function __construct(
        public readonly TaskListModel $taskList,
        public readonly array $taskIds,
    ) {}
}
