<?php

namespace App\Domains\Project\Actions\UpdateProject;

use App\Domains\Project\AuditRecords\ProjectUpdatedAuditRecord;
use App\Domains\Project\Models\ProjectModel;
use App\Libs\AuditTrail\Facades\AuditTrail;

class UpdateProjectHandler
{
    public function handle(UpdateProjectCommand $command): ProjectModel
    {
        $command->project->update([
            'name'        => $command->name,
            'icon'        => $command->icon,
            'status'      => $command->status,
            'description' => $command->description,
            'start_date'  => $command->startDate,
            'end_date'    => $command->endDate,
        ]);

        $changed = ProjectUpdatedAuditRecord::reportableColumns(array_keys($command->project->getChanges()));

        if ($changed !== []) {
            AuditTrail::capture(new ProjectUpdatedAuditRecord($command->project, $changed));
        }

        if ($command->tagIds !== null) {
            $command->project->tags()->sync($command->tagIds);
        }

        return $command->project->fresh();
    }
}
