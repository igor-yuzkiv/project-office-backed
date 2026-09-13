<?php

namespace App\Domains\Project\Actions\PinProject;

use App\Domains\Project\Models\ProjectModel;
use App\Domains\User\Models\UserModel;

class PinProjectCommand
{
    public function __construct(
        public readonly UserModel $user,
        public readonly ProjectModel $project,
    ) {}
}
