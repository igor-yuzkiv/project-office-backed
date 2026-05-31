<?php

namespace App\Domains\Task\Actions\CreateTask;

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
        $taskKey = $this->taskKeyResolver->resolve($command->project);

        return TaskModel::create([
            'project_id'      => $command->project->id,
            'task_list_id'    => $command->taskListId,
            'key'             => $taskKey->value,
            'sequence_number' => $taskKey->sequenceNumber,
            'name'            => $command->name,
            'description'     => $command->description,
            'priority'        => $command->priority->value,
            'status'          => TaskStatus::Open->value,
        ]);
    }
}
