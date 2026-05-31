<?php

namespace App\Domains\Task\Actions\DeleteTask;

use App\Domains\Task\Models\TaskModel;

class DeleteTaskHandler
{
    public function handle(TaskModel $task): void
    {
        $task->delete();
    }
}
