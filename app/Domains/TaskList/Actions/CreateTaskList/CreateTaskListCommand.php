<?php

namespace App\Domains\TaskList\Actions\CreateTaskList;

use App\Domains\Project\Models\ProjectModel;

class CreateTaskListCommand
{
    public function __construct(
        public readonly ProjectModel $project,
        public readonly string $name,
    ) {}
}
