<?php

namespace App\Domains\Task\Actions\SyncTaskOwners;

use App\Domains\Task\Enums\TaskOwnerRole;

class TaskOwnerItemDTO
{
    public function __construct(
        public readonly string $userId,
        public readonly ?TaskOwnerRole $role,
        public readonly bool $isPrimary,
    ) {}
}
