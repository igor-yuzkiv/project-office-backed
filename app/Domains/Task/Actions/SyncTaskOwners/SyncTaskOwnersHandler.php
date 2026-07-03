<?php

namespace App\Domains\Task\Actions\SyncTaskOwners;

use App\Domains\Task\Exceptions\InvalidTaskOwnerAssignmentException;
use App\Domains\Task\Models\TaskOwnerModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SyncTaskOwnersHandler
{
    public function handle(SyncTaskOwnersCommand $command): Collection
    {
        $primaryCount = count(array_filter($command->owners, fn (TaskOwnerItemDTO $o) => $o->isPrimary));

        if ($primaryCount > 1) {
            throw InvalidTaskOwnerAssignmentException::multiplePrimaryOwners();
        }

        DB::transaction(function () use ($command) {
            TaskOwnerModel::where('task_id', $command->taskId)->delete();

            foreach ($command->owners as $owner) {
                TaskOwnerModel::create([
                    'task_id'    => $command->taskId,
                    'user_id'    => $owner->userId,
                    'role'       => $owner->role?->value,
                    'is_primary' => $owner->isPrimary,
                ]);
            }
        });

        return TaskOwnerModel::where('task_id', $command->taskId)
            ->with('user')
            ->get();
    }
}
