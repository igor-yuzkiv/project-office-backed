<?php

namespace App\Domains\Task;

use App\Domains\Project\Models\ProjectModel;
use App\Domains\Task\Models\TaskModel;
use App\Domains\Task\ValueObjects\TaskKey;

class TaskKeyResolver
{
    // TODO: wrap in a database lock to prevent race conditions under concurrent requests
    public function resolve(ProjectModel $project): TaskKey
    {
        $sequenceNumber = (int) TaskModel::where('project_id', $project->id)->max('sequence_number') + 1;

        return new TaskKey(
            sequenceNumber: $sequenceNumber,
            value: $project->prefix.'-'.$sequenceNumber,
        );
    }
}
