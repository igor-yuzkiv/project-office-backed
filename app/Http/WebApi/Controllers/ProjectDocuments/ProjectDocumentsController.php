<?php

namespace App\Http\WebApi\Controllers\ProjectDocuments;

use App\Domains\Project\Models\ProjectModel;
use App\Domains\ProjectDocument\Actions\CreateProjectDocument\CreateProjectDocumentHandler;
use App\Domains\ProjectDocument\Actions\UpdateProjectDocument\UpdateProjectDocumentHandler;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Http\Shared\Resources\ProjectDocuments\ProjectDocumentOverviewResource;
use App\Http\Shared\Resources\ProjectDocuments\ProjectDocumentResource;
use App\Http\WebApi\Controllers\ResourceController;
use App\Http\WebApi\Requests\ProjectDocuments\StoreProjectDocumentRequest;
use App\Http\WebApi\Requests\ProjectDocuments\UpdateProjectDocumentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectDocumentsController extends ResourceController
{
    public function __construct(
        private readonly CreateProjectDocumentHandler $createHandler,
        private readonly UpdateProjectDocumentHandler $updateHandler,
    ) {}

    private const array FULL_RELATIONS = ['tags', 'tasks', 'project', 'createdBy', 'updatedBy'];

    protected function getAllowedIncludes(): array
    {
        return self::FULL_RELATIONS;
    }

    public function index(ProjectModel $project): AnonymousResourceCollection
    {
        $includes = $this->resolveIncludes(required: ['tags'], requested: $this->parseRequestedIncludes());

        $documents = ProjectDocumentModel::where('project_id', $project->id)
            ->with($includes)
            ->get();

        return ProjectDocumentOverviewResource::collection($documents);
    }

    public function show(ProjectDocumentModel $projectDocument): ProjectDocumentResource
    {
        $includes = $this->resolveIncludes(required: self::FULL_RELATIONS, requested: $this->parseRequestedIncludes());
        $projectDocument->load($includes);

        return new ProjectDocumentResource($projectDocument);
    }

    public function store(StoreProjectDocumentRequest $request, ProjectModel $project): JsonResponse
    {
        $document = $this->createHandler->handle($request->toCommand($project));
        $document->load(self::FULL_RELATIONS);

        return (new ProjectDocumentResource($document))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateProjectDocumentRequest $request, ProjectDocumentModel $projectDocument): ProjectDocumentResource
    {
        $document = $this->updateHandler->handle($request->toCommand($projectDocument));
        $document->load(self::FULL_RELATIONS);

        return new ProjectDocumentResource($document);
    }
}
