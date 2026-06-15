<?php

namespace App\Domains\Task\Actions\CreateTask;

use App\Domains\Project\Models\ProjectModel;
use App\Domains\Task\Enums\TaskStatus;
use App\Domains\Task\Models\TaskModel;
use App\Domains\Task\TaskKeyResolver;

class CreateTaskHandler
{
    public function __construct(
        private readonly TaskKeyResolver $taskKeyResolver,
    ) {}

    public function handle(CreateTaskCommand $command): TaskModel
    {
        /** @var ProjectModel $project */
        $project = ProjectModel::findOrFail($command->projectId);
        $taskKey = $this->taskKeyResolver->resolve($project);

        $task = TaskModel::create([
            'project_id'      => $command->projectId,
            'task_list_id'    => $command->taskListId,
            'key'             => $taskKey->value,
            'sequence_number' => $taskKey->sequenceNumber,
            'name'            => $command->name,
            'description'     => $command->description,
            'start_date'      => $command->startDate,
            'due_date'        => $command->dueDate,
            'priority'        => $command->priority->value,
            'status'          => TaskStatus::Open->value,
        ]);

        if ($command->tagIds !== null) {
            $task->tags()->sync($command->tagIds);
        }

        return $task;
    }
}
