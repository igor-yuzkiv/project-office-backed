<?php

namespace App\Http\WebApi\Controllers\ProjectDocuments;

use App\Domains\Annotation\Actions\CreateAnnotation\CreateAnnotationCommand;
use App\Domains\Annotation\Actions\CreateAnnotation\CreateAnnotationHandler;
use App\Domains\Annotation\DTO\BlockAnchorDTO;
use App\Domains\ProjectDocument\Models\ProjectDocumentVersionModel;
use App\Domains\User\Models\UserModel;
use App\Http\Shared\Resources\Annotations\AnnotationResource;
use App\Http\WebApi\Requests\Annotation\StoreAnnotationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectDocumentVersionAnnotationsController
{
    public function __construct(
        private readonly CreateAnnotationHandler $createHandler,
    ) {}

    public function index(ProjectDocumentVersionModel $projectDocumentVersion): AnonymousResourceCollection
    {
        $annotations = $projectDocumentVersion->annotations()
            ->with('author')
            ->orderBy('created_at')
            ->get();

        return AnnotationResource::collection($annotations);
    }

    public function store(StoreAnnotationRequest $request, ProjectDocumentVersionModel $projectDocumentVersion): JsonResponse
    {
        /** @var UserModel $user */
        $user = $request->user();

        $annotation = $this->createHandler->handle(new CreateAnnotationCommand(
            annotatable: $projectDocumentVersion,
            author: $user,
            content: $request->validated('content'),
            anchor: BlockAnchorDTO::fromArray($request->validated('anchor')),
            textSnapshot: $request->validated('text_snapshot'),
        ));

        $annotation->load('author');

        return (new AnnotationResource($annotation))
            ->response()
            ->setStatusCode(201);
    }
}
