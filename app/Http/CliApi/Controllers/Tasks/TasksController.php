<?php

namespace App\Http\CliApi\Controllers\Tasks;

use App\Domains\Project\Models\ProjectModel;
use App\Domains\Tag\Actions\CreateTags\CreateTagsHandler;
use App\Domains\Task\Actions\CreateTask\CreateTaskHandler;
use App\Domains\Task\Actions\UpdateTask\UpdateTaskHandler;
use App\Domains\Task\Models\TaskModel;
use App\Http\CliApi\Requests\Tasks\StoreTaskRequest;
use App\Http\CliApi\Requests\Tasks\UpdateTaskRequest;
use App\Http\Shared\Resources\Tasks\TaskOverviewResource;
use App\Http\Shared\Resources\Tasks\TaskResource;
use App\Http\WebApi\Controllers\ResourceController;
use App\Http\WebApi\Requests\Shared\SearchRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class TasksController extends ResourceController
{
    public function __construct(
        private readonly CreateTaskHandler $createHandler,
        private readonly UpdateTaskHandler $updateHandler,
        private readonly CreateTagsHandler $createTagsHandler,
    ) {}

    protected function getAllowedIncludes(): array
    {
        return ['project', 'taskList', 'createdBy', 'updatedBy', 'tags'];
    }

    public function index(ProjectModel $project): AnonymousResourceCollection
    {
        $pagination = $this->getPaginationParams();
        $sort = $this->getSortParams();
        $includes = $this->resolveIncludes(required: ['createdBy', 'updatedBy', 'tags'], requested: $this->parseRequestedIncludes());

        $tasks = TaskModel::where('project_id', $project->id)
            ->with($includes)
            ->orderBy($sort->field, $sort->direction)
            ->paginate($pagination->perPage, page: $pagination->page);

        return TaskOverviewResource::collection($tasks);
    }

    public function search(ProjectModel $project, SearchRequest $request): AnonymousResourceCollection
    {
        $sort = $this->getSortParams();
        $pagination = $this->getPaginationParams();
        $includes = $this->resolveIncludes(required: ['createdBy', 'updatedBy', 'tags'], requested: $this->parseRequestedIncludes());

        $tasks = TaskModel::search((string) $request->input('query', ''))
            ->orderBy($sort->field, $sort->direction)
            ->query(function (Builder $q) use ($project, $request, $includes): Builder {
                /** @var Builder<TaskModel> $q */
                return $q
                    ->where('project_id', $project->id)
                    ->with($includes)
                    ->filter((array) $request->input('filters', []));
            })
            ->paginate($pagination->perPage, 'page', $pagination->page);

        return TaskOverviewResource::collection($tasks);
    }

    public function store(ProjectModel $project, StoreTaskRequest $request): JsonResponse
    {
        $tagDtos = $request->getTagDtos();
        $tagIds = $tagDtos->isNotEmpty()
            ? $this->createTagsHandler->handle($tagDtos)->pluck('id')->all()
            : null;

        $task = $this->createHandler->handle($request->toCommand($project, $tagIds));
        $task->load(['createdBy', 'updatedBy']);

        return (new TaskResource($task))
            ->response()
            ->setStatusCode(201);
    }

    public function show(ProjectModel $project, TaskModel $task): JsonResource
    {
        $task->load($this->resolveIncludes(required: ['createdBy', 'updatedBy', 'project', 'taskList', 'tags'], requested: $this->parseRequestedIncludes()));

        return new TaskResource($task);
    }

    public function update(ProjectModel $project, TaskModel $task, UpdateTaskRequest $request): JsonResource
    {
        $task = $this->updateHandler->handle($request->toCommand($task));
        $task->load(['createdBy', 'updatedBy']);

        return new TaskResource($task);
    }
}
