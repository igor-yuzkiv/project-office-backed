<?php

namespace App\Http\CliApi\Controllers\ProjectDocuments;

use App\Domains\Project\Models\ProjectModel;
use App\Domains\ProjectDocument\Actions\CreateProjectDocument\CreateProjectDocumentHandler;
use App\Domains\ProjectDocument\Actions\UpdateProjectDocument\UpdateProjectDocumentHandler;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Domains\ProjectDocument\Queries\GetProjectDocumentAncestorPathQuery;
use App\Domains\Tag\Actions\CreateTags\CreateTagsHandler;
use App\Http\CliApi\Requests\ProjectDocuments\StoreProjectDocumentRequest;
use App\Http\CliApi\Requests\ProjectDocuments\UpdateProjectDocumentRequest;
use App\Http\CliApi\Resources\ProjectDocuments\ProjectDocumentResource;
use Illuminate\Http\JsonResponse;

class ProjectDocumentsController
{
    public function __construct(
        private readonly CreateProjectDocumentHandler $createHandler,
        private readonly UpdateProjectDocumentHandler $updateHandler,
        private readonly CreateTagsHandler $createTagsHandler,
        private readonly GetProjectDocumentAncestorPathQuery $ancestorPathQuery,
    ) {}

    public function show(ProjectModel $project, ProjectDocumentModel $document): ProjectDocumentResource
    {
        $document->load('tags');

        return $this->resource($document);
    }

    public function store(ProjectModel $project, StoreProjectDocumentRequest $request): JsonResponse
    {
        $tagDtos = $request->getTagDtos();
        $tagIds = $tagDtos->isNotEmpty()
            ? $this->createTagsHandler->handle($tagDtos)->pluck('id')->all()
            : null;

        $document = $this->createHandler->handle($request->toCommand($project, $tagIds));
        $document->load('tags');

        return $this->resource($document)
            ->response()
            ->setStatusCode(201);
    }

    public function update(ProjectModel $project, ProjectDocumentModel $document, UpdateProjectDocumentRequest $request): ProjectDocumentResource
    {
        $tagIds = null;
        if ($request->has('tags')) {
            $tagDtos = $request->getTagDtos();
            $tagIds = $tagDtos->isNotEmpty()
                ? $this->createTagsHandler->handle($tagDtos)->pluck('id')->all()
                : [];
        }

        $document = $this->updateHandler->handle($request->toCommand($document, $tagIds));
        $document->load('tags');

        return $this->resource($document);
    }

    private function resource(ProjectDocumentModel $document): ProjectDocumentResource
    {
        return (new ProjectDocumentResource($document))
            ->withPath($this->ancestorPathQuery->handle($document));
    }
}
