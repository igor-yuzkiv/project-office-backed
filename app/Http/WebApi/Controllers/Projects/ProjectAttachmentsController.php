<?php

namespace App\Http\WebApi\Controllers\Projects;

use App\Domains\Attachment\Actions\UploadAttachment\UploadAttachmentCommand;
use App\Domains\Attachment\Actions\UploadAttachment\UploadAttachmentHandler;
use App\Domains\Project\Models\ProjectModel;
use App\Http\Shared\Resources\Attachments\AttachmentResource;
use App\Http\WebApi\Requests\Projects\StoreProjectAttachmentRequest;
use Illuminate\Http\JsonResponse;

class ProjectAttachmentsController
{
    public function __construct(
        private readonly UploadAttachmentHandler $uploadHandler,
    ) {}

    public function store(StoreProjectAttachmentRequest $request, ProjectModel $project): JsonResponse
    {
        $command = new UploadAttachmentCommand(
            file: $request->file('file'),
            attachable: $project,
            role: $request->validated('role'),
        );

        $attachment = $this->uploadHandler->handle($command);
        $attachment->load(['createdBy', 'updatedBy']);

        return (new AttachmentResource($attachment))
            ->response()
            ->setStatusCode(201);
    }
}
