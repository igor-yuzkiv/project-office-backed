<?php

namespace App\Domains\Task\Actions\CreateTask;

use App\Domains\Task\Enums\TaskPriority;

class CreateTaskCommand
{
    public function __construct(
        public readonly string $projectId,
        public readonly string $name,
        public readonly TaskPriority $priority = TaskPriority::None,
        public readonly ?string $taskListId = null,
        public readonly ?string $description = null,
        public readonly ?array $tagIds = null,
    ) {}
}
