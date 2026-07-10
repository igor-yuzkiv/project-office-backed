<?php

namespace App\Domains\Task\Actions\DeleteTask;

use App\Domains\Task\Models\TaskModel;

class DeleteTaskCommand
{
    public function __construct(
        public readonly TaskModel $task,
    ) {}
}
