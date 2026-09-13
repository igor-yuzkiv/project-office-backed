<?php

namespace App\Domains\Project\Queries;

use App\Domains\Task\Queries\CountTasksPerStatusQuery as CountTasksPerOwnerQuery;

class CountTasksPerStatusQuery extends CountTasksPerOwnerQuery
{
    protected function ownerColumn(): string
    {
        return 'project_id';
    }
}
