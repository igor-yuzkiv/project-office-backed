<?php

namespace App\Domains\Project\Actions\DeleteProject;

use App\Domains\Project\AuditRecords\ProjectDeletedAuditRecord;
use App\Libs\AuditTrail\Facades\AuditTrail;

class DeleteProjectHandler
{
    public function handle(DeleteProjectCommand $command): void
    {
        // A deleted model keeps its attributes and key in memory, so the record can read them
        // after the row is gone. Capturing after delete() is what matters: a delete that throws
        // leaves no event behind.
        $record = new ProjectDeletedAuditRecord($command->project);

        $command->project->delete();

        AuditTrail::capture($record);
    }
}
