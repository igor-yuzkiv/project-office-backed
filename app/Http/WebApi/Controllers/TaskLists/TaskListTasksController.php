<?php

namespace App\Http\WebApi\Controllers\TaskLists;

use App\Domains\TaskList\Actions\AddTasksToTaskList\AddTasksToTaskListHandler;
use App\Domains\TaskList\Models\TaskListModel;
use App\Http\Shared\Resources\Tasks\TaskOverviewResource;
use App\Http\WebApi\Requests\TaskLists\AddTasksToTaskListRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskListTasksController
{
    public function __construct(
        private readonly AddTasksToTaskListHandler $addHandler,
    ) {}

    public function store(AddTasksToTaskListRequest $request, TaskListModel $taskList): AnonymousResourceCollection
    {
        $tasks = $this->addHandler->handle($request->toCommand($taskList));

        return TaskOverviewResource::collection($tasks);
    }
}
