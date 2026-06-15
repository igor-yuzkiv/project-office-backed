<?php

namespace App\Domains\Task\Actions\UpdateTask;

use App\Domains\Task\Enums\TaskPriority;
use App\Domains\Task\Enums\TaskStatus;
use App\Domains\Task\Models\TaskModel;
use Illuminate\Support\Carbon;

class UpdateTaskCommand
{
    public function __construct(
        public readonly TaskModel $task,
        public readonly ?string $taskListId = null,
        public readonly ?string $name = null,
        public readonly ?string $description = null,
        public readonly TaskPriority $priority = TaskPriority::None,
        public readonly ?TaskStatus $status = null,
        public readonly ?Carbon $startDate = null,
        public readonly ?Carbon $dueDate = null,
        public readonly ?array $tagIds = null,
    ) {}
}
