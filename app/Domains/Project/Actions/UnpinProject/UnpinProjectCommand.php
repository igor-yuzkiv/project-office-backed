<?php

namespace App\Domains\Project\Actions\UnpinProject;

use App\Domains\Project\Models\ProjectModel;
use App\Domains\User\Models\UserModel;

class UnpinProjectCommand
{
    public function __construct(
        public readonly UserModel $user,
        public readonly ProjectModel $project,
    ) {}
}
