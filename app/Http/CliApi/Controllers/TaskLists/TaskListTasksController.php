<?php

namespace App\Http\CliApi\Controllers\TaskLists;

use App\Domains\Project\Models\ProjectModel;
use App\Domains\TaskList\Models\TaskListModel;
use App\Http\Shared\Resources\Tasks\TaskOverviewResource;
use App\Http\WebApi\Controllers\ResourceController;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskListTasksController extends ResourceController
{
    /** The agent-facing task list payload is fixed: nothing here is selectable by the client. */
    protected function getAllowedIncludes(): array
    {
        return [];
    }

    /**
     * A task list is a plan document, so its tasks come back whole — every status, including
     * Backlog and Closed, in plan order: by name, because names carry the plan's numbering, with
     * `sequence_number` breaking ties so pagination stays stable. That is the same rule the task
     * list show endpoint follows, and it is deliberately not the project task index rule.
     */
    public function index(ProjectModel $project, TaskListModel $taskList): AnonymousResourceCollection
    {
        $pagination = $this->getPaginationParams();

        $tasks = $taskList->tasks()
            ->with(['createdBy', 'updatedBy', 'tags'])
            ->orderBy('name')
            ->orderBy('sequence_number')
            ->paginate($pagination->perPage, page: $pagination->page);

        return TaskOverviewResource::collection($tasks);
    }
}
