<?php

namespace App\Domains\Project\Actions\DeleteProject;

class DeleteProjectHandler
{
    public function handle(DeleteProjectCommand $command): void
    {
        $command->project->delete();
    }
}
