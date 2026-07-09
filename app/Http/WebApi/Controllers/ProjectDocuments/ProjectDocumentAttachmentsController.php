<?php

namespace App\Http\WebApi\Controllers\ProjectDocuments;

use App\Domains\Attachment\Actions\UploadAttachment\UploadAttachmentCommand;
use App\Domains\Attachment\Actions\UploadAttachment\UploadAttachmentHandler;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Http\Shared\Resources\Attachments\AttachmentResource;
use App\Http\WebApi\Requests\ProjectDocuments\StoreProjectDocumentAttachmentRequest;
use Illuminate\Http\JsonResponse;

class ProjectDocumentAttachmentsController
{
    public function __construct(
        private readonly UploadAttachmentHandler $uploadHandler,
    ) {}

    public function store(StoreProjectDocumentAttachmentRequest $request, ProjectDocumentModel $projectDocument): JsonResponse
    {
        $command = new UploadAttachmentCommand(
            file: $request->file('file'),
            attachable: $projectDocument,
            role: $request->validated('role'),
        );

        $attachment = $this->uploadHandler->handle($command);
        $attachment->load(['createdBy', 'updatedBy']);

        return (new AttachmentResource($attachment))
            ->response()
            ->setStatusCode(201);
    }
}
