<?php

namespace App\Http\CliApi\Controllers\ProjectDocuments;

use App\Domains\Project\Models\ProjectModel;
use App\Domains\ProjectDocument\Actions\CreateProjectDocument\CreateProjectDocumentHandler;
use App\Domains\ProjectDocument\Actions\UpdateProjectDocument\UpdateProjectDocumentHandler;
use App\Domains\ProjectDocument\Actions\WriteProjectDocumentContent\WriteProjectDocumentContentCommand;
use App\Domains\ProjectDocument\Actions\WriteProjectDocumentContent\WriteProjectDocumentContentHandler;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Domains\ProjectDocument\Queries\GetProjectDocumentAncestorPathQuery;
use App\Domains\Tag\Actions\CreateTags\CreateTagsCommand;
use App\Domains\Tag\Actions\CreateTags\CreateTagsHandler;
use App\Http\CliApi\Requests\ProjectDocuments\StoreProjectDocumentRequest;
use App\Http\CliApi\Requests\ProjectDocuments\UpdateProjectDocumentRequest;
use App\Http\CliApi\Resources\ProjectDocuments\ProjectDocumentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ProjectDocumentsController
{
    public function __construct(
        private readonly CreateProjectDocumentHandler $createHandler,
        private readonly UpdateProjectDocumentHandler $updateHandler,
        private readonly WriteProjectDocumentContentHandler $writeContentHandler,
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
            ? $this->createTagsHandler->handle(new CreateTagsCommand($tagDtos))->pluck('id')->all()
            : null;

        $document = DB::transaction(function () use ($project, $request, $tagIds): ProjectDocumentModel {
            $document = $this->createHandler->handle($request->toCommand($project, $tagIds));

            if ($request->hasContent()) {
                $this->writeContentHandler->handle(new WriteProjectDocumentContentCommand(
                    document: $document,
                    content: $request->content(),
                    authorId: auth()->id(),
                    recordsUpdate: false,
                ));
                $document->refresh();
            }

            return $document;
        });

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
                ? $this->createTagsHandler->handle(new CreateTagsCommand($tagDtos))->pluck('id')->all()
                : [];
        }

        $document = $this->updateHandler->handle($request->toCommand($document, $tagIds));

        if ($request->hasContent()) {
            $this->writeContentHandler->handle(new WriteProjectDocumentContentCommand(
                document: $document,
                content: $request->content(),
                authorId: auth()->id(),
            ));
            $document->refresh();
        }

        $document->load('tags');

        return $this->resource($document);
    }

    private function resource(ProjectDocumentModel $document): ProjectDocumentResource
    {
        return (new ProjectDocumentResource($document))
            ->withPath($this->ancestorPathQuery->handle($document));
    }
}
