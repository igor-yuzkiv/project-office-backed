<?php

namespace App\Domains\TaskList\Queries;

use App\Domains\Task\Queries\CountTasksPerStatusQuery as CountTasksPerOwnerQuery;

class CountTasksPerStatusQuery extends CountTasksPerOwnerQuery
{
    protected function ownerColumn(): string
    {
        return 'task_list_id';
    }
}
