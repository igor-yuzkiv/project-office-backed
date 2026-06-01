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
use App\Http\Controllers\Controller;
use App\Http\Requests\Shared\SearchRequest;
use App\Http\Requests\Tasks\StoreTaskRequest;
use App\Http\Requests\Tasks\UpdateTaskRequest;
use App\Http\Resources\Tasks\TaskResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TasksController extends Controller
{
    public function __construct(
        private readonly CreateTaskHandler $createHandler,
        private readonly UpdateTaskHandler $updateHandler,
        private readonly DeleteTaskHandler $deleteHandler,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $pagination = $this->getPaginationParams();
        $sort = $this->getSortParams();

        $tasks = TaskModel::with(['createdBy', 'updatedBy'])
            ->orderBy($sort->field, $sort->direction)
            ->paginate($pagination->perPage, page: $pagination->page);

        return TaskResource::collection($tasks);
    }

    public function search(SearchRequest $request): AnonymousResourceCollection
    {
        $sort = $this->getSortParams();
        $pagination = $this->getPaginationParams();

        $tasks = TaskModel::search((string) $request->input('query', ''))
            ->orderBy($sort->field, $sort->direction)
            ->query(function (Builder $q) use ($request): Builder {
                /** @var Builder<TaskModel> $q */
                return $q->with(['createdBy', 'updatedBy'])->filter((array) $request->input('filters', []));
            })
            ->paginate($pagination->perPage, 'page', $pagination->page);

        return TaskResource::collection($tasks);
    }

    public function show(TaskModel $task): TaskResource
    {
        $task->load(['createdBy', 'updatedBy']);

        return new TaskResource($task);
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $command = new CreateTaskCommand(
            projectId: $request->validated('project_id'),
            name: $request->validated('name'),
            priority: TaskPriority::from((int) $request->validated('priority')),
            taskListId: $request->validated('task_list_id'),
            description: $request->validated('description'),
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
            priority: $request->has('priority') ? TaskPriority::from((int) $request->validated('priority')) : null,
            status: $request->has('status') ? TaskStatus::from($request->validated('status')) : null,
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
