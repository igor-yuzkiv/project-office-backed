<?php

namespace App\Http\Controllers\TaskLists;

use App\Domains\TaskList\Actions\CreateTaskList\CreateTaskListCommand;
use App\Domains\TaskList\Actions\CreateTaskList\CreateTaskListHandler;
use App\Domains\TaskList\Actions\DeleteTaskList\DeleteTaskListHandler;
use App\Domains\TaskList\Actions\UpdateTaskList\UpdateTaskListCommand;
use App\Domains\TaskList\Actions\UpdateTaskList\UpdateTaskListHandler;
use App\Domains\TaskList\Models\TaskListModel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shared\SearchRequest;
use App\Http\Requests\TaskLists\StoreTaskListRequest;
use App\Http\Requests\TaskLists\UpdateTaskListRequest;
use App\Http\Resources\TaskLists\TaskListResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskListsController extends Controller
{
    public function __construct(
        private readonly CreateTaskListHandler $createHandler,
        private readonly UpdateTaskListHandler $updateHandler,
        private readonly DeleteTaskListHandler $deleteHandler,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $pagination = $this->getPaginationParams();
        $sort = $this->getSortParams();

        $taskLists = TaskListModel::with(['createdBy', 'updatedBy'])
            ->orderBy($sort->field, $sort->direction)
            ->paginate($pagination->perPage, page: $pagination->page);

        return TaskListResource::collection($taskLists);
    }

    public function search(SearchRequest $request): AnonymousResourceCollection
    {
        $sort = $this->getSortParams();
        $pagination = $this->getPaginationParams();

        $taskLists = TaskListModel::search((string) $request->input('query', ''))
            ->orderBy($sort->field, $sort->direction)
            ->query(function (Builder $q) use ($request): Builder {
                /** @var Builder<TaskListModel> $q */
                return $q->with(['createdBy', 'updatedBy'])->filter((array) $request->input('filters', []));
            })
            ->paginate($pagination->perPage, 'page', $pagination->page);

        return TaskListResource::collection($taskLists);
    }

    public function show(TaskListModel $taskList): TaskListResource
    {
        $taskList->load(['createdBy', 'updatedBy']);

        return new TaskListResource($taskList);
    }

    public function store(StoreTaskListRequest $request): JsonResponse
    {
        $command = new CreateTaskListCommand(
            projectId: $request->validated('project_id'),
            name: $request->validated('name'),
        );

        $taskList = $this->createHandler->handle($command);
        $taskList->load(['createdBy', 'updatedBy']);

        return (new TaskListResource($taskList))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateTaskListRequest $request, TaskListModel $taskList): TaskListResource
    {
        $command = new UpdateTaskListCommand(
            taskList: $taskList,
            name: $request->validated('name'),
        );

        $taskList = $this->updateHandler->handle($command);
        $taskList->load(['createdBy', 'updatedBy']);

        return new TaskListResource($taskList);
    }

    public function destroy(TaskListModel $taskList): JsonResponse
    {
        $this->deleteHandler->handle($taskList);

        return response()->json(['message' => 'Task list deleted.']);
    }
}
