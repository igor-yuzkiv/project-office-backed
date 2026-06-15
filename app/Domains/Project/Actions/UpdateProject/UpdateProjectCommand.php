<?php

namespace App\Domains\Project\Actions\UpdateProject;

use App\Domains\Project\Enums\ProjectStatus;
use App\Domains\Project\Models\ProjectModel;
use Illuminate\Support\Carbon;

class UpdateProjectCommand
{
    public function __construct(
        public readonly ProjectModel $project,
        public readonly ?string $name = null,
        public readonly ?ProjectStatus $status = null,
        public readonly ?string $description = null,
        public readonly ?Carbon $startDate = null,
        public readonly ?Carbon $endDate = null,
        public readonly ?array $tagIds = null,
    ) {}
}
