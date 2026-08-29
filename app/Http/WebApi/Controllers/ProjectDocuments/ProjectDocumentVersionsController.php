<?php

namespace App\Http\WebApi\Controllers\ProjectDocuments;

use App\Domains\ProjectDocument\Actions\Version\CreateProjectDocumentVersion\CreateProjectDocumentVersionHandler;
use App\Domains\ProjectDocument\Actions\Version\DeleteProjectDocumentVersion\DeleteProjectDocumentVersionCommand;
use App\Domains\ProjectDocument\Actions\Version\DeleteProjectDocumentVersion\DeleteProjectDocumentVersionHandler;
use App\Domains\ProjectDocument\Actions\Version\SetProjectDocumentPrimaryVersion\SetProjectDocumentPrimaryVersionHandler;
use App\Domains\ProjectDocument\Actions\Version\UpdateProjectDocumentVersion\UpdateProjectDocumentVersionHandler;
use App\Domains\ProjectDocument\Actions\Version\UpdateProjectDocumentVersionContent\UpdateProjectDocumentVersionContentHandler;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Domains\ProjectDocument\Models\ProjectDocumentVersionModel;
use App\Http\Shared\Resources\ProjectDocuments\ProjectDocumentResource;
use App\Http\Shared\Resources\ProjectDocuments\ProjectDocumentVersionResource;
use App\Http\WebApi\Requests\ProjectDocuments\SetProjectDocumentPrimaryVersionRequest;
use App\Http\WebApi\Requests\ProjectDocuments\StoreProjectDocumentVersionRequest;
use App\Http\WebApi\Requests\ProjectDocuments\UpdateProjectDocumentVersionContentRequest;
use App\Http\WebApi\Requests\ProjectDocuments\UpdateProjectDocumentVersionRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectDocumentVersionsController
{
    public function __construct(
        private readonly CreateProjectDocumentVersionHandler $createHandler,
        private readonly UpdateProjectDocumentVersionContentHandler $updateContentHandler,
        private readonly UpdateProjectDocumentVersionHandler $updateHandler,
        private readonly DeleteProjectDocumentVersionHandler $deleteHandler,
        private readonly SetProjectDocumentPrimaryVersionHandler $setPrimaryHandler,
    ) {}

    public function index(ProjectDocumentModel $projectDocument): AnonymousResourceCollection
    {
        $versions = $projectDocument->versions()->with('author')->get();
        $this->markPrimary($projectDocument, $versions);

        return ProjectDocumentVersionResource::collection($versions);
    }

    public function store(StoreProjectDocumentVersionRequest $request, ProjectDocumentModel $projectDocument): JsonResponse
    {
        $version = $this->createHandler->handle($request->toCommand());
        $version->load('author');

        $this->markPrimary($projectDocument->refresh(), new Collection([$version]));

        return (new ProjectDocumentVersionResource($version))
            ->response()
            ->setStatusCode(201);
    }

    public function updateContent(
        UpdateProjectDocumentVersionContentRequest $request,
        ProjectDocumentModel $projectDocument,
        ProjectDocumentVersionModel $version,
    ): ProjectDocumentVersionResource {
        $version = $this->updateContentHandler->handle($request->toCommand($version))->load('author');
        $this->markPrimary($projectDocument, new Collection([$version]));

        return new ProjectDocumentVersionResource($version);
    }

    public function update(
        UpdateProjectDocumentVersionRequest $request,
        ProjectDocumentVersionModel $projectDocumentVersion,
    ): ProjectDocumentVersionResource {
        $version = $this->updateHandler->handle($request->toCommand($projectDocumentVersion))->load('author');
        $this->markPrimary($version->document, new Collection([$version]));

        return new ProjectDocumentVersionResource($version);
    }

    public function destroy(ProjectDocumentVersionModel $projectDocumentVersion): JsonResponse
    {
        $this->deleteHandler->handle(new DeleteProjectDocumentVersionCommand($projectDocumentVersion));

        return response()->json(status: 204);
    }

    public function setPrimary(SetProjectDocumentPrimaryVersionRequest $request, ProjectDocumentModel $projectDocument): ProjectDocumentResource
    {
        return new ProjectDocumentResource($this->setPrimaryHandler->handle($request->toCommand()));
    }

    /**
     * @param  Collection<int, ProjectDocumentVersionModel>  $versions
     */
    private function markPrimary(ProjectDocumentModel $document, Collection $versions): void
    {
        $primaryId = $document->effectiveVersion()?->id;

        foreach ($versions as $version) {
            $version->isPrimary = $version->id === $primaryId;
        }
    }
}
