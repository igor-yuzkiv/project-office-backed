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
                'status' => $command->status,
            ],
            fn ($value) => $value !== null
        );

        $command->project->update($data);

        if ($command->tagIds !== null) {
            $command->project->tags()->sync($command->tagIds);
        }

        return $command->project->fresh();
    }
}
