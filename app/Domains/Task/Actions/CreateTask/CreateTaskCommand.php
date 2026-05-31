<?php

namespace App\Domains\Task\Actions\CreateTask;

use App\Domains\Project\Models\ProjectModel;
use App\Domains\Task\Enums\TaskPriority;

class CreateTaskCommand
{
    public function __construct(
        public readonly ProjectModel $project,
        public readonly string $name,
        public readonly TaskPriority $priority,
        public readonly ?string $taskListId = null,
        public readonly ?string $description = null,
    ) {}
}
