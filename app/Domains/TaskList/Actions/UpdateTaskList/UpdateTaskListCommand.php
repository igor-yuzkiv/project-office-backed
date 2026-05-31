<?php

namespace App\Domains\TaskList\Actions\UpdateTaskList;

use App\Domains\TaskList\Models\TaskListModel;

class UpdateTaskListCommand
{
    public function __construct(
        public readonly TaskListModel $taskList,
        public readonly ?string $name = null,
    ) {}
}
