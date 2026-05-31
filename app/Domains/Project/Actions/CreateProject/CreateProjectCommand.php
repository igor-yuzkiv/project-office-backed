<?php

namespace App\Domains\Project\Actions\CreateProject;

class CreateProjectCommand
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $prefix = null,
    ) {}
}
