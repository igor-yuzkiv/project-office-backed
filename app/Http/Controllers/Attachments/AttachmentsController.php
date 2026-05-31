<?php

namespace App\Http\Controllers\Attachments;

use App\Domains\Attachment\Actions\UploadAttachment\UploadAttachmentCommand;
use App\Domains\Attachment\Actions\UploadAttachment\UploadAttachmentHandler;
use App\Domains\Shared\ValueObjects\EntityRef;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attachments\UploadAttachmentRequest;
use App\Http\Resources\Attachments\AttachmentResource;
use Illuminate\Http\JsonResponse;

class AttachmentsController extends Controller
{
    public function __construct(
        private readonly UploadAttachmentHandler $uploadHandler,
    ) {}

    public function store(UploadAttachmentRequest $request): JsonResponse
    {
        $entityType = $request->validated('entity_type');
        $entityId = $request->validated('entity_id');

        $entityRef = ($entityType !== null && $entityId !== null)
            ? new EntityRef(id: $entityId, module: $entityType)
            : null;

        $command = new UploadAttachmentCommand(
            file: $request->file('file'),
            entityRef: $entityRef,
            role: $request->validated('role'),
        );

        $attachment = $this->uploadHandler->handle($command);
        $attachment->load(['createdBy', 'updatedBy']);

        return (new AttachmentResource($attachment))
            ->response()
            ->setStatusCode(201);
    }
}
