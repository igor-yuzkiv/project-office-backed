<?php

namespace App\Domains\Task\Actions\CliAgentWorkflow\StartTask;

use App\Domains\Task\Models\TaskModel;
use App\Domains\User\Models\UserModel;

class StartTaskCommand
{
    public function __construct(
        public readonly TaskModel $task,
        public readonly UserModel $author,
        public readonly int $commentsLimit = 10,
        public readonly ?string $comment = null,
    ) {}
}
