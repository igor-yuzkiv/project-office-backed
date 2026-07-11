<?php

namespace App\Domains\Task\Actions\CliAgentWorkflow\HandoffTask;

use App\Domains\Task\Models\TaskModel;
use App\Domains\User\Models\UserModel;

class HandoffTaskCommand
{
    public function __construct(
        public readonly TaskModel $task,
        public readonly UserModel $author,
        public readonly string $resolution,
    ) {}
}
