<?php

namespace App\Domains\Task\Actions\CliAgentWorkflow\CheckpointTask;

use App\Domains\Task\Models\TaskModel;
use App\Domains\User\Models\UserModel;

class CheckpointTaskCommand
{
    public function __construct(
        public readonly TaskModel $task,
        public readonly UserModel $author,
        public readonly string $subject,
        public readonly string $comment,
    ) {}
}
