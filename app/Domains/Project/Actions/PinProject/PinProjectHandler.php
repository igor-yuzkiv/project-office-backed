<?php

namespace App\Domains\Project\Actions\PinProject;

class PinProjectHandler
{
    public function handle(PinProjectCommand $command): void
    {
        $command->user->pinnedProjects()->syncWithoutDetaching([
            $command->project->id => ['created_at' => now()],
        ]);
    }
}
