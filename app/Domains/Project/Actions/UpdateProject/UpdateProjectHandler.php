<?php

namespace App\Domains\Project\Actions\UpdateProject;

use App\Domains\Project\Models\ProjectModel;

class UpdateProjectHandler
{
    public function handle(UpdateProjectCommand $command): ProjectModel
    {
        $command->project->update([
            'name'        => $command->name,
            'status'      => $command->status,
            'description' => $command->description,
            'start_date'  => $command->startDate,
            'end_date'    => $command->endDate,
        ]);

        if ($command->tagIds !== null) {
            $command->project->tags()->sync($command->tagIds);
        }

        return $command->project->fresh();
    }
}
