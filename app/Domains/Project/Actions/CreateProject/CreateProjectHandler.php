<?php

namespace App\Domains\Project\Actions\CreateProject;

use App\Domains\Project\Models\ProjectModel;

class CreateProjectHandler
{
    public function handle(CreateProjectCommand $command): ProjectModel
    {
        $project = ProjectModel::create([
            'name'   => $command->name,
            'prefix' => $command->prefix,
            'status' => $command->status,
        ]);

        if ($command->tagIds !== null) {
            $project->tags()->sync($command->tagIds);
        }

        return $project;
    }
}
