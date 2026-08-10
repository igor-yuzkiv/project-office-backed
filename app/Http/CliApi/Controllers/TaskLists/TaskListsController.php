<?php

namespace App\Http\CliApi\Controllers\TaskLists;

use App\Domains\Project\Models\ProjectModel;
use App\Domains\Tag\Actions\CreateTags\CreateTagsCommand;
use App\Domains\Tag\Actions\CreateTags\CreateTagsHandler;
use App\Domains\TaskList\Actions\CreateTaskList\CreateTaskListHandler;
use App\Domains\TaskList\Actions\UpdateTaskList\UpdateTaskListHandler;
use App\Domains\TaskList\Models\TaskListModel;
use App\Http\CliApi\Requests\TaskLists\StoreTaskListRequest;
use App\Http\CliApi\Requests\TaskLists\UpdateTaskListRequest;
use App\Http\Shared\Resources\TaskLists\TaskListOverviewResource;
use App\Http\Shared\Resources\TaskLists\TaskListResource;
use App\Http\WebApi\Controllers\ResourceController;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskListsController extends ResourceController
{
    public function __construct(
        private readonly CreateTaskListHandler $createHandler,
        private readonly UpdateTaskListHandler $updateHandler,
        private readonly CreateTagsHandler $createTagsHandler,
    ) {}

    /** The agent-facing task list payload is fixed: nothing here is selectable by the client. */
    protected function getAllowedIncludes(): array
    {
        return [];
    }

    public function index(ProjectModel $project): AnonymousResourceCollection
    {
        $pagination = $this->getPaginationParams();

        $taskLists = TaskListModel::where('project_id', $project->id)
            ->orderBy('sequence_number')
            ->paginate($pagination->perPage, page: $pagination->page);

        return TaskListOverviewResource::collection($taskLists);
    }

    /**
     * A task list is a plan document, so its tasks always travel with it — every status
     * included, in plan order, without a second request from the CLI.
     */
    public function show(ProjectModel $project, TaskListModel $taskList): JsonResource
    {
        $taskList->load([
            'createdBy',
            'updatedBy',
            'tags',
            'tasks' => fn (HasMany $query) => $query->orderBy('sequence_number'),
        ]);

        return new TaskListResource($taskList);
    }

    public function store(ProjectModel $project, StoreTaskListRequest $request): JsonResponse
    {
        $taskList = $this->createHandler->handle($request->toCommand($project, $this->resolveTagIds($request)));
        $taskList->load(['createdBy', 'updatedBy', 'tags']);

        return (new TaskListResource($taskList))
            ->response()
            ->setStatusCode(201);
    }

    public function update(ProjectModel $project, TaskListModel $taskList, UpdateTaskListRequest $request): JsonResource
    {
        $taskList = $this->updateHandler->handle($request->toCommand($taskList, $this->resolveTagIds($request)));
        $taskList->load(['createdBy', 'updatedBy', 'tags']);

        return new TaskListResource($taskList);
    }

    /**
     * Tags arrive as a comma-separated string of names and are created on demand.
     * Null means the payload carried no tags at all, which leaves them untouched.
     */
    private function resolveTagIds(StoreTaskListRequest|UpdateTaskListRequest $request): ?array
    {
        if (!$request->has('tags')) {
            return null;
        }

        $tagDtos = $request->getTagDtos();

        return $tagDtos->isNotEmpty()
            ? $this->createTagsHandler->handle(new CreateTagsCommand($tagDtos))->pluck('id')->all()
            : [];
    }
}
