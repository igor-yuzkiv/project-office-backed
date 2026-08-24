<?php

namespace App\Domains\Project\Actions\CreateProject;

use App\Domains\Project\AuditRecords\ProjectCreatedAuditRecord;
use App\Domains\Project\Models\ProjectModel;
use App\Libs\AuditTrail\Facades\AuditTrail;

class CreateProjectHandler
{
    public function handle(CreateProjectCommand $command): ProjectModel
    {
        $project = ProjectModel::create([
            'name'        => $command->name,
            'prefix'      => $command->prefix,
            'icon'        => $command->icon,
            'status'      => $command->status,
            'description' => $command->description,
            'start_date'  => $command->startDate,
            'end_date'    => $command->endDate,
        ]);

        if ($command->tagIds !== null) {
            $project->tags()->sync($command->tagIds);
        }

        AuditTrail::capture(new ProjectCreatedAuditRecord($project));

        return $project;
    }
}
