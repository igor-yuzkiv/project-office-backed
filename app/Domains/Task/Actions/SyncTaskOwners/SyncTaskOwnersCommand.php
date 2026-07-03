<?php

namespace App\Domains\Task\Actions\SyncTaskOwners;

class SyncTaskOwnersCommand
{
    /**
     * @param  TaskOwnerItemDTO[]  $owners
     */
    public function __construct(
        public readonly string $taskId,
        public readonly array $owners,
    ) {}
}
