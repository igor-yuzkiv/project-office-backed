<?php

namespace App\Domains\TaskList\Actions\UpdateTaskList;

use App\Domains\TaskList\Enums\TaskListStatus;
use App\Domains\TaskList\Models\TaskListModel;

class UpdateTaskListCommand
{
    public function __construct(
        public readonly TaskListModel $taskList,
        public readonly ?string $name = null,
        public readonly ?TaskListStatus $status = null,
        public readonly ?string $description = null,
        /** Distinguishes "description omitted" from "description cleared to null". */
        public readonly bool $descriptionProvided = false,
        public readonly ?array $tagIds = null,
    ) {}
}
