<?php

namespace App\Domains\Project\Actions\CreateProject;

use App\Domains\Project\Enums\ProjectStatus;
use Illuminate\Support\Carbon;

class CreateProjectCommand
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $prefix = null,
        public readonly ProjectStatus $status = ProjectStatus::DRAFT,
        public readonly ?string $description = null,
        public readonly ?Carbon $startDate = null,
        public readonly ?Carbon $endDate = null,
        public readonly ?array $tagIds = null,
    ) {}
}
