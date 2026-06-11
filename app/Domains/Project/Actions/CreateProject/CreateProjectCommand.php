<?php

namespace App\Domains\Project\Actions\CreateProject;

use App\Domains\Project\Enums\ProjectStatus;

class CreateProjectCommand
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $prefix = null,
        public readonly ProjectStatus $status = ProjectStatus::DRAFT,
    ) {}
}
