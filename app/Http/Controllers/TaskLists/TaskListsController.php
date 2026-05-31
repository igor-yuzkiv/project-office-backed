<?php

namespace App\Http\Controllers\TaskLists;

use App\Domains\Project\Models\ProjectModel;
use App\Domains\TaskList\Actions\CreateTaskList\CreateTaskListCommand;
use App\Domains\TaskList\Actions\CreateTaskList\CreateTaskListHandler;
use App\Domains\TaskList\Actions\DeleteTaskList\DeleteTaskListHandler;
use App\Domains\TaskList\Actions\UpdateTaskList\UpdateTaskListCommand;
use App\Domains\TaskList\Actions\UpdateTaskList\UpdateTaskListHandler;
use App\Domains\TaskList\Models\TaskListModel;
use App\Http\Controllers\Controller;
use App\Http\Requests\TaskLists\StoreTaskListRequest;
use App\Http\Requests\TaskLists\UpdateTaskListRequest;
use App\Http\Resources\TaskLists\TaskListResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskListsController extends Controller
{
    public function __construct(
        private readonly CreateTaskListHandler $createHandler,
        private readonly UpdateTaskListHandler $updateHandler,
        private readonly DeleteTaskListHandler $deleteHandler,
    ) {}

    public function index(ProjectModel $project): AnonymousResourceCollection
    {
        $pagination = $this->getPaginationParams();
        $sort = $this->getSortParams();

        $taskLists = TaskListModel::with(['createdBy', 'updatedBy'])
            ->where('project_id', $project->id)
            ->orderBy($sort->field, $sort->direction)
            ->paginate($pagination->perPage, page: $pagination->page);

        return TaskListResource::collection($taskLists);
    }

    public function show(ProjectModel $project, TaskListModel $taskList): TaskListResource
    {
        $taskList->load(['createdBy', 'updatedBy']);

        return new TaskListResource($taskList);
    }

    public function store(StoreTaskListRequest $request, ProjectModel $project): JsonResponse
    {
        $command = new CreateTaskListCommand(
            project: $project,
            name: $request->validated('name'),
        );

        $taskList = $this->createHandler->handle($command);
        $taskList->load(['createdBy', 'updatedBy']);

        return (new TaskListResource($taskList))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateTaskListRequest $request, ProjectModel $project, TaskListModel $taskList): TaskListResource
    {
        $command = new UpdateTaskListCommand(
            taskList: $taskList,
            name: $request->validated('name'),
        );

        $taskList = $this->updateHandler->handle($command);
        $taskList->load(['createdBy', 'updatedBy']);

        return new TaskListResource($taskList);
    }

    public function destroy(ProjectModel $project, TaskListModel $taskList): JsonResponse
    {
        $this->deleteHandler->handle($taskList);

        return response()->json(['message' => 'Task list deleted.']);
    }
}
