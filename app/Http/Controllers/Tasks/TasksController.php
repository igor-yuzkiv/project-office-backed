<?php

namespace App\Http\Controllers\Tasks;

use App\Domains\Task\Actions\CreateTask\CreateTaskCommand;
use App\Domains\Task\Actions\CreateTask\CreateTaskHandler;
use App\Domains\Task\Actions\DeleteTask\DeleteTaskHandler;
use App\Domains\Task\Actions\UpdateTask\UpdateTaskCommand;
use App\Domains\Task\Actions\UpdateTask\UpdateTaskHandler;
use App\Domains\Task\Enums\TaskPriority;
use App\Domains\Task\Enums\TaskStatus;
use App\Domains\Task\Models\TaskModel;
use App\Http\Controllers\ResourceController;
use App\Http\Requests\Shared\SearchRequest;
use App\Http\Requests\Tasks\StoreTaskRequest;
use App\Http\Requests\Tasks\UpdateTaskRequest;
use App\Http\Resources\Tasks\TaskOverviewResource;
use App\Http\Resources\Tasks\TaskResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TasksController extends ResourceController
{
    public function __construct(
        private readonly CreateTaskHandler $createHandler,
        private readonly UpdateTaskHandler $updateHandler,
        private readonly DeleteTaskHandler $deleteHandler,
    ) {}

    protected function getAllowedIncludes(): array
    {
        return ['project', 'taskList', 'createdBy', 'updatedBy', 'tags'];
    }

    public function index(): AnonymousResourceCollection
    {
        $pagination = $this->getPaginationParams();
        $sort = $this->getSortParams();
        $includes = $this->resolveIncludes(required: ['createdBy', 'updatedBy', 'tags'], requested: $this->parseRequestedIncludes());

        $tasks = TaskModel::with($includes)
            ->orderBy($sort->field, $sort->direction)
            ->paginate($pagination->perPage, page: $pagination->page);

        return TaskOverviewResource::collection($tasks);
    }

    public function search(SearchRequest $request): AnonymousResourceCollection
    {
        $sort = $this->getSortParams();
        $pagination = $this->getPaginationParams();
        $includes = $this->resolveIncludes(required: ['createdBy', 'updatedBy', 'tags'], requested: $this->parseRequestedIncludes());

        $tasks = TaskModel::search((string) $request->input('query', ''))
            ->orderBy($sort->field, $sort->direction)
            ->query(function (Builder $q) use ($request, $includes): Builder {
                /** @var Builder<TaskModel> $q */
                return $q
                    ->with($includes)
                    ->filter((array) $request->input('filters', []));
            })
            ->paginate($pagination->perPage, 'page', $pagination->page);

        return TaskOverviewResource::collection($tasks);
    }

    public function show(TaskModel $task): TaskResource
    {
        $task->load($this->resolveIncludes(required: ['createdBy', 'updatedBy', 'project', 'taskList', 'tags'], requested: $this->parseRequestedIncludes()));

        return new TaskResource($task);
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $rawPriority = $request->validated('priority');

        $command = new CreateTaskCommand(
            projectId: $request->validated('project_id'),
            name: $request->validated('name'),
            priority: $rawPriority !== null ? TaskPriority::from((int) $rawPriority) : null,
            taskListId: $request->validated('task_list_id'),
            description: $request->validated('description'),
            tagIds: $request->validated('tag_ids'),
        );

        $task = $this->createHandler->handle($command);
        $task->load(['createdBy', 'updatedBy']);

        return (new TaskResource($task))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateTaskRequest $request, TaskModel $task): TaskResource
    {
        $command = new UpdateTaskCommand(
            task: $task,
            taskListId: $request->validated('task_list_id'),
            name: $request->validated('name'),
            description: $request->validated('description'),
            priority: ($p = $request->validated('priority')) !== null ? TaskPriority::from((int) $p) : null,
            status: ($s = $request->validated('status')) !== null ? TaskStatus::from($s) : null,
            tagIds: $request->validated('tag_ids'),
        );

        $task = $this->updateHandler->handle($command);
        $task->load(['createdBy', 'updatedBy']);

        return new TaskResource($task);
    }

    public function destroy(TaskModel $task): JsonResponse
    {
        $this->deleteHandler->handle($task);

        return response()->json(['message' => 'Task deleted.']);
    }
}
