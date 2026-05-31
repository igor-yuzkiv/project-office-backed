<?php

namespace App\Domains\Project\Actions\UpdateProject;

use App\Domains\Project\Models\ProjectModel;

class UpdateProjectHandler
{
    public function handle(UpdateProjectCommand $command): ProjectModel
    {
        $data = array_filter(
            [
                'name'   => $command->name,
                'prefix' => $command->prefix,
            ],
            fn ($value) => $value !== null
        );

        $command->project->update($data);

        return $command->project->fresh();
    }
}
