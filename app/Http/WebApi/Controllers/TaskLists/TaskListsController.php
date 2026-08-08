<?php

namespace App\Http\WebApi\Controllers\TaskLists;

use App\Domains\TaskList\Actions\CreateTaskList\CreateTaskListHandler;
use App\Domains\TaskList\Actions\DeleteTaskList\DeleteTaskListCommand;
use App\Domains\TaskList\Actions\DeleteTaskList\DeleteTaskListHandler;
use App\Domains\TaskList\Actions\UpdateTaskList\UpdateTaskListHandler;
use App\Domains\TaskList\Models\TaskListModel;
use App\Http\Shared\Resources\TaskLists\TaskListResource;
use App\Http\WebApi\Controllers\ResourceController;
use App\Http\WebApi\Requests\Shared\SearchRequest;
use App\Http\WebApi\Requests\TaskLists\StoreTaskListRequest;
use App\Http\WebApi\Requests\TaskLists\UpdateTaskListRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskListsController extends ResourceController
{
    public function __construct(
        private readonly CreateTaskListHandler $createHandler,
        private readonly UpdateTaskListHandler $updateHandler,
        private readonly DeleteTaskListHandler $deleteHandler,
    ) {}

    protected function getAllowedIncludes(): array
    {
        return ['tasks', 'project', 'tags', 'createdBy', 'updatedBy'];
    }

    public function index(): AnonymousResourceCollection
    {
        $pagination = $this->getPaginationParams();
        $sort = $this->getSortParams();

        $includes = $this->resolveIncludes(required: ['createdBy', 'updatedBy'], requested: $this->parseRequestedIncludes());

        $taskLists = TaskListModel::withCount('tasks')
            ->with($includes)
            ->orderBy($sort->field, $sort->direction)
            ->paginate($pagination->perPage, page: $pagination->page);

        return TaskListResource::collection($taskLists);
    }

    public function search(SearchRequest $request): AnonymousResourceCollection
    {
        $sort = $this->getSortParams();
        $pagination = $this->getPaginationParams();

        $includes = $this->resolveIncludes(required: ['createdBy', 'updatedBy'], requested: $this->parseRequestedIncludes());

        $taskLists = TaskListModel::search((string) $request->input('query', ''))
            ->orderBy($sort->field, $sort->direction)
            ->query(function (Builder $q) use ($request, $includes): Builder {
                /** @var Builder<TaskListModel> $q */
                return $q->withCount('tasks')->with($includes)->filter((array) $request->input('filters', []));
            })
            ->paginate($pagination->perPage, 'page', $pagination->page);

        return TaskListResource::collection($taskLists);
    }

    public function show(TaskListModel $taskList): TaskListResource
    {
        $taskList->loadCount('tasks');
        $taskList->load($this->resolveIncludes(required: ['createdBy', 'updatedBy', 'project', 'tags'], requested: $this->parseRequestedIncludes()));

        return new TaskListResource($taskList);
    }

    public function store(StoreTaskListRequest $request): JsonResponse
    {
        $taskList = $this->createHandler->handle($request->toCommand());
        $taskList->load(['createdBy', 'updatedBy', 'tags']);

        return (new TaskListResource($taskList))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateTaskListRequest $request, TaskListModel $taskList): TaskListResource
    {
        $taskList = $this->updateHandler->handle($request->toCommand($taskList));
        $taskList->load(['createdBy', 'updatedBy', 'tags']);

        return new TaskListResource($taskList);
    }

    public function destroy(TaskListModel $taskList): JsonResponse
    {
        $this->deleteHandler->handle(new DeleteTaskListCommand($taskList));

        return response()->json(['message' => 'Task list deleted.']);
    }
}
