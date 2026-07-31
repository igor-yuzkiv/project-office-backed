<?php

namespace App\Domains\Task\Actions\BulkUpdateTaskStatus;

use App\Domains\Task\Enums\TaskStatus;

class BulkUpdateTaskStatusCommand
{
    /**
     * @param  string[]  $taskIds
     */
    public function __construct(
        public readonly array $taskIds,
        public readonly TaskStatus $status,
    ) {}
}
