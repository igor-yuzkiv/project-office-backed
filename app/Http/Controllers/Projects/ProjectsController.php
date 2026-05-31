<?php

namespace App\Http\Controllers\Projects;

use App\Domains\Project\Actions\CreateProject\CreateProjectCommand;
use App\Domains\Project\Actions\CreateProject\CreateProjectHandler;
use App\Domains\Project\Actions\DeleteProject\DeleteProjectHandler;
use App\Domains\Project\Actions\UpdateProject\UpdateProjectCommand;
use App\Domains\Project\Actions\UpdateProject\UpdateProjectHandler;
use App\Domains\Project\Models\ProjectModel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Projects\StoreProjectRequest;
use App\Http\Requests\Projects\UpdateProjectRequest;
use App\Http\Resources\Projects\ProjectResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectsController extends Controller
{
    public function __construct(
        private readonly CreateProjectHandler $createHandler,
        private readonly UpdateProjectHandler $updateHandler,
        private readonly DeleteProjectHandler $deleteHandler,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $pagination = $this->getPaginationParams();
        $sort = $this->getSortParams();

        $projects = ProjectModel::with(['createdBy', 'updatedBy'])
            ->orderBy($sort->field, $sort->direction)
            ->paginate($pagination->perPage, page: $pagination->page);

        return ProjectResource::collection($projects);
    }

    public function show(ProjectModel $project): ProjectResource
    {
        $project->load(['createdBy', 'updatedBy']);

        return new ProjectResource($project);
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $command = new CreateProjectCommand(
            name: $request->validated('name'),
            prefix: $request->validated('prefix'),
        );

        $project = $this->createHandler->handle($command);
        $project->load(['createdBy', 'updatedBy']);

        return (new ProjectResource($project))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateProjectRequest $request, ProjectModel $project): ProjectResource
    {
        $command = new UpdateProjectCommand(
            project: $project,
            name: $request->validated('name'),
            prefix: $request->validated('prefix'),
        );

        $project = $this->updateHandler->handle($command);
        $project->load(['createdBy', 'updatedBy']);

        return new ProjectResource($project);
    }

    public function destroy(ProjectModel $project): JsonResponse
    {
        $this->deleteHandler->handle($project);

        return response()->json(['message' => 'Project deleted.']);
    }
}
