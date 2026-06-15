<?php

namespace App\Http\Controllers\Projects;

use App\Domains\Project\Actions\CreateProject\CreateProjectHandler;
use App\Domains\Project\Actions\DeleteProject\DeleteProjectHandler;
use App\Domains\Project\Actions\UpdateProject\UpdateProjectHandler;
use App\Domains\Project\Models\ProjectModel;
use App\Http\Controllers\ResourceController;
use App\Http\Requests\Projects\StoreProjectRequest;
use App\Http\Requests\Projects\UpdateProjectRequest;
use App\Http\Requests\Shared\SearchRequest;
use App\Http\Resources\Projects\ProjectOverviewResource;
use App\Http\Resources\Projects\ProjectResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectsController extends ResourceController
{
    public function __construct(
        private readonly CreateProjectHandler $createHandler,
        private readonly UpdateProjectHandler $updateHandler,
        private readonly DeleteProjectHandler $deleteHandler,
    ) {}

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
                    ->filter((array) $request->input('filters', []));
            })
            ->paginate($pagination->perPage, 'page', $pagination->page);

        return ProjectOverviewResource::collection($projects);
    }

    public function show(ProjectModel $project): ProjectResource
    {
        $project->load($this->resolveIncludes(required: ['createdBy', 'updatedBy', 'archivedBy', 'tags'], requested: $this->parseRequestedIncludes()));

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
        $this->deleteHandler->handle($project);

        return response()->json(['message' => 'Project deleted.']);
    }
}
