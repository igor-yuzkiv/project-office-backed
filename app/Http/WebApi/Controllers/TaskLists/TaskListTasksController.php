<?php

namespace App\Http\WebApi\Controllers\TaskLists;

use App\Domains\TaskList\Actions\AddTasksToTaskList\AddTasksToTaskListHandler;
use App\Domains\TaskList\Models\TaskListModel;
use App\Http\Shared\Resources\Tasks\TaskOverviewResource;
use App\Http\WebApi\Controllers\ResourceController;
use App\Http\WebApi\Requests\TaskLists\AddTasksToTaskListRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskListTasksController extends ResourceController
{
    public function __construct(
        private readonly AddTasksToTaskListHandler $addHandler,
    ) {}

    /** The list is read as a whole; nothing about its payload is selectable by the client. */
    protected function getAllowedIncludes(): array
    {
        return [];
    }

    /**
     * A task list is a plan document, so its tasks come back whole — every status, including
     * Backlog and Closed.
     *
     * The order is the name, because names carry the plan's own numbering ("1. Subject") and
     * that is the order a person reads the list in. `sequence_number` breaks ties so pagination
     * stays stable when two tasks share a name.
     */
    public function index(TaskListModel $taskList): AnonymousResourceCollection
    {
        $pagination = $this->getPaginationParams();

        $tasks = $taskList->tasks()
            ->orderBy('name')
            ->orderBy('sequence_number')
            ->paginate($pagination->perPage, page: $pagination->page);

        return TaskOverviewResource::collection($tasks);
    }

    public function store(AddTasksToTaskListRequest $request, TaskListModel $taskList): AnonymousResourceCollection
    {
        $tasks = $this->addHandler->handle($request->toCommand($taskList));

        return TaskOverviewResource::collection($tasks);
    }
}
