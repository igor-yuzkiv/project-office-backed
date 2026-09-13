<?php

namespace App\Domains\Project\Actions\UnpinProject;

class UnpinProjectHandler
{
    public function handle(UnpinProjectCommand $command): void
    {
        $command->user->pinnedProjects()->detach($command->project->id);
    }
}
