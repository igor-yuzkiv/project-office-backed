<?php

namespace App\Domains\TaskList\Actions\CreateTaskList;

use App\Domains\Project\Models\ProjectModel;
use App\Domains\TaskList\Models\TaskListModel;
use App\Domains\TaskList\Services\TaskListKeyResolver;

class CreateTaskListHandler
{
    public function __construct(
        private readonly TaskListKeyResolver $taskListKeyResolver,
    ) {}

    public function handle(CreateTaskListCommand $command): TaskListModel
    {
        /** @var ProjectModel $project */
        $project = ProjectModel::findOrFail($command->projectId);
        $taskListKey = $this->taskListKeyResolver->resolve($project);

        $taskList = TaskListModel::create([
            'project_id'      => $command->projectId,
            'key'             => $taskListKey->value,
            'sequence_number' => $taskListKey->sequenceNumber,
            'name'            => $command->name,
            'status'          => $command->status->value,
            'description'     => $command->description,
        ]);

        if ($command->tagIds !== null) {
            $taskList->tags()->sync($command->tagIds);
        }

        return $taskList;
    }
}
