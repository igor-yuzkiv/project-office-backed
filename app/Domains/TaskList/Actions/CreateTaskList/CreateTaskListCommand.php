<?php

namespace App\Domains\TaskList\Actions\CreateTaskList;

class CreateTaskListCommand
{
    public function __construct(
        public readonly string $projectId,
        public readonly string $name,
    ) {}
}
