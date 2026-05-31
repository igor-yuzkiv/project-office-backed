<?php

namespace App\Domains\Project\Actions\DeleteProject;

use App\Domains\Project\Models\ProjectModel;

class DeleteProjectHandler
{
    public function handle(ProjectModel $project): void
    {
        $project->delete();
    }
}
