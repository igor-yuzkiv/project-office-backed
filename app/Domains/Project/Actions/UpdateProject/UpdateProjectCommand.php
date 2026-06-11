<?php

namespace App\Domains\Project\Actions\UpdateProject;

use App\Domains\Project\Enums\ProjectStatus;
use App\Domains\Project\Models\ProjectModel;

class UpdateProjectCommand
{
    public function __construct(
        public readonly ProjectModel $project,
        public readonly ?string $name = null,
        public readonly ?string $prefix = null,
        public readonly ?ProjectStatus $status = null,
    ) {}
}
