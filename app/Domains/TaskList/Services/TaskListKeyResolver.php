<?php

namespace App\Domains\TaskList\Services;

use App\Domains\Project\Models\ProjectModel;
use App\Domains\TaskList\Models\TaskListModel;
use App\Domains\TaskList\ValueObjects\TaskListKey;

class TaskListKeyResolver
{
    // TODO: wrap in a database lock to prevent race conditions under concurrent requests
    public function resolve(ProjectModel $project): TaskListKey
    {
        $sequenceNumber = (int) TaskListModel::where('project_id', $project->id)->max('sequence_number') + 1;

        return new TaskListKey(
            sequenceNumber: $sequenceNumber,
            value: $project->prefix.'-TL-'.$sequenceNumber,
        );
    }
}
