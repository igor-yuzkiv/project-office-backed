<?php

namespace App\Domains\Task\Actions\DeleteTask;

use App\Domains\Task\AuditRecords\TaskDeletedAuditRecord;
use App\Libs\AuditTrail\Facades\AuditTrail;

class DeleteTaskHandler
{
    public function handle(DeleteTaskCommand $command): void
    {
        // A deleted model keeps its attributes and key in memory, so the record can read them
        // after the row is gone. Capturing after delete() is what matters: a delete that throws
        // leaves no event behind.
        $record = new TaskDeletedAuditRecord($command->task);

        $command->task->delete();

        AuditTrail::capture($record);
    }
}
