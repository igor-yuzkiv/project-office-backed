<?php

namespace App\Domains\Project\Actions\DeleteProject;

use Lorisleiva\Actions\Concerns\AsAction;

class DeleteProjectHandler
{
    use AsAction;

    public function handle(DeleteProjectCommand $command): void
    {
        $command->project->delete();
    }
}
