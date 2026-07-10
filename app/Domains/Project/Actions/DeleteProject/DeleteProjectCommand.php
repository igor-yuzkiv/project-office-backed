<?php

namespace App\Domains\Project\Actions\DeleteProject;

use App\Domains\Project\Models\ProjectModel;

class DeleteProjectCommand
{
    public function __construct(
        public readonly ProjectModel $project,
    ) {}
}
