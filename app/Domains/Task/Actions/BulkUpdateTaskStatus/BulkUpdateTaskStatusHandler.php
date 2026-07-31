<?php

namespace App\Domains\Task\Actions\BulkUpdateTaskStatus;

use App\Domains\Task\Models\TaskModel;
use Illuminate\Support\Facades\DB;

class BulkUpdateTaskStatusHandler
{
    /**
     * Saves each task individually so the auditable columns and model observers still run;
     * a query-builder mass update would bypass both.
     */
    public function handle(BulkUpdateTaskStatusCommand $command): int
    {
        return DB::transaction(function () use ($command): int {
            $tasks = TaskModel::whereIn('id', $command->taskIds)->get();

            foreach ($tasks as $task) {
                $task->update(['status' => $command->status->value]);
            }

            return $tasks->count();
        });
    }
}
