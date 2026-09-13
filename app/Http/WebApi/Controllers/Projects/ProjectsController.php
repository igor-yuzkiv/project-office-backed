<?php

namespace App\Http\WebApi\Controllers\Projects;

use App\Domains\Project\Actions\CreateProject\CreateProjectHandler;
use App\Domains\Project\Actions\DeleteProject\DeleteProjectCommand;
use App\Domains\Project\Actions\DeleteProject\DeleteProjectHandler;
use App\Domains\Project\Actions\PinProject\PinProjectCommand;
use App\Domains\Project\Actions\PinProject\PinProjectHandler;
use App\Domains\Project\Actions\UnpinProject\UnpinProjectCommand;
use App\Domains\Project\Actions\UnpinProject\UnpinProjectHandler;
use App\Domains\Project\Actions\UpdateProject\UpdateProjectHandler;
use App\Domains\Project\Models\ProjectModel;
use App\Domains\Project\Queries\CountTasksPerStatusQuery;
use App\Http\Shared\Resources\Projects\ProjectOverviewResource;
use App\Http\Shared\Resources\Projects\ProjectResource;
use App\Http\WebApi\Controllers\ResourceController;
use App\Http\WebApi\Requests\Projects\StoreProjectRequest;
use App\Http\WebApi\Requests\Projects\UpdateProjectRequest;
use App\Http\WebApi\Requests\Shared\SearchRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProjectsController extends ResourceController
{
    public function __construct(
        private readonly CreateProjectHandler $createHandler,
        private readonly UpdateProjectHandler $updateHandler,
        private readonly DeleteProjectHandler $deleteHandler,
        private readonly PinProjectHandler $pinHandler,
        private readonly UnpinProjectHandler $unpinHandler,
        private readonly CountTasksPerStatusQuery $countTasksPerStatus,
    ) {}

    /** The project card counts what a project holds, so every list response carries these. */
    private const array COUNTED_RELATIONS = ['documents', 'taskLists', 'tasks'];

    /** Whether the current user pinned the project; the pin state lives per user, not on the project. */
    private function pinnedByCurrentUser(): array
    {
        return ['pinnedBy as is_pinned' => fn (Builder $q) => $q->where('user_id', auth()->id())];
    }

    protected function getAllowedIncludes(): array
    {
        return ['createdBy', 'updatedBy', 'archivedBy', 'tags', 'tasks', 'taskLists'];
    }

    public function index(): AnonymousResourceCollection
    {
        $pagination = $this->getPaginationParams();
        $sort = $this->getSortParams();

        $includes = $this->resolveIncludes(required: ['createdBy', 'updatedBy', 'tags'], requested: $this->parseRequestedIncludes());

        $projects = ProjectModel::with($includes)
            ->withCount(self::COUNTED_RELATIONS)
            ->withExists($this->pinnedByCurrentUser())
            ->orderBy($sort->field, $sort->direction)
            ->paginate($pagination->perPage, page: $pagination->page);

        return ProjectOverviewResource::collection($projects);
    }

    public function search(SearchRequest $request): AnonymousResourceCollection
    {
        $sort = $this->getSortParams();
        $pagination = $this->getPaginationParams();

        $includes = $this->resolveIncludes(required: ['createdBy', 'updatedBy', 'tags'], requested: $this->parseRequestedIncludes());

        $projects = ProjectModel::search((string) $request->input('query', ''))
            ->orderBy($sort->field, $sort->direction)
            ->query(function (Builder $q) use ($request, $includes): Builder {
                /** @var Builder<ProjectModel> $q */
                return $q
                    ->with($includes)
                    ->withCount(self::COUNTED_RELATIONS)
                    ->withExists($this->pinnedByCurrentUser())
                    ->filter((array) $request->input('filters', []));
            })
            ->paginate($pagination->perPage, 'page', $pagination->page);

        return ProjectOverviewResource::collection($projects);
    }

    /** The sidebar reads this: pinned projects in pin order, each with its tasks counted per status. */
    public function pinned(Request $request): AnonymousResourceCollection
    {
        /** @var Collection<int, ProjectModel> $projects */
        $projects = $request->user()->pinnedProjects()
            ->with(['createdBy', 'updatedBy', 'tags'])
            ->withCount(self::COUNTED_RELATIONS)
            ->orderBy('user_pinned_projects.created_at')
            ->get();

        $counts = $this->countTasksPerStatus->handle($projects->modelKeys());

        foreach ($projects as $project) {
            $project->setAttribute('is_pinned', true);
            $project->setAttribute('task_status_counts', $counts[$project->id]);
        }

        return ProjectOverviewResource::collection($projects);
    }

    public function show(ProjectModel $project): ProjectResource
    {
        $project->load($this->resolveIncludes(required: ['createdBy', 'updatedBy', 'archivedBy', 'tags'], requested: $this->parseRequestedIncludes()));
        $project->loadExists($this->pinnedByCurrentUser());

        return new ProjectResource($project);
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = $this->createHandler->handle($request->toCommand());
        $project->load(['createdBy', 'updatedBy']);

        return (new ProjectResource($project))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateProjectRequest $request, ProjectModel $project): ProjectResource
    {
        $project = $this->updateHandler->handle($request->toCommand($project));
        $project->load(['createdBy', 'updatedBy', 'archivedBy']);

        return new ProjectResource($project);
    }

    public function destroy(ProjectModel $project): JsonResponse
    {
        $this->deleteHandler->handle(new DeleteProjectCommand($project));

        return response()->json(['message' => 'Project deleted.']);
    }

    public function pin(Request $request, ProjectModel $project): Response
    {
        $this->pinHandler->handle(new PinProjectCommand($request->user(), $project));

        return response()->noContent();
    }

    public function unpin(Request $request, ProjectModel $project): Response
    {
        $this->unpinHandler->handle(new UnpinProjectCommand($request->user(), $project));

        return response()->noContent();
    }
}
