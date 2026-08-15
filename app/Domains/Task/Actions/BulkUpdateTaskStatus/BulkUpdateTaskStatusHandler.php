<?php

namespace App\Domains\Task\Actions\BulkUpdateTaskStatus;

use App\Domains\Task\AuditRecords\TasksBulkStatusChangedAuditRecord;
use App\Domains\Task\Models\TaskModel;
use App\Libs\AuditTrail\Facades\AuditTrail;
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
            // Ordered by key: the audit description lists these keys verbatim, and a feed line
            // must not depend on whatever order the database happens to return.
            $tasks = TaskModel::whereIn('id', $command->taskIds)->orderBy('key')->get();

            foreach ($tasks as $task) {
                $task->update(['status' => $command->status->value]);
            }

            if ($tasks->isNotEmpty()) {
                AuditTrail::capture(new TasksBulkStatusChangedAuditRecord(
                    $tasks->pluck('key')->all(),
                    $command->status,
                ));
            }

            return $tasks->count();
        });
    }
}
