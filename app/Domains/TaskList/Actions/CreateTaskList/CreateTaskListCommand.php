<?php

namespace App\Domains\TaskList\Actions\CreateTaskList;

use App\Domains\TaskList\Enums\TaskListStatus;

class CreateTaskListCommand
{
    public function __construct(
        public readonly string $projectId,
        public readonly string $name,
        public readonly TaskListStatus $status = TaskListStatus::Open,
        public readonly ?string $description = null,
        public readonly ?array $tagIds = null,
    ) {}
}
