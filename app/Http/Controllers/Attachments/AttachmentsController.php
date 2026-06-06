<?php

namespace App\Http\Controllers\Attachments;

use App\Domains\Attachment\Actions\UploadAttachment\UploadAttachmentCommand;
use App\Domains\Attachment\Actions\UploadAttachment\UploadAttachmentHandler;
use App\Domains\Attachment\Models\AttachmentModel;
use App\Domains\Attachment\Services\AttachmentStorageService;
use App\Domains\Shared\ValueObjects\EntityRef;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attachments\UploadAttachmentRequest;
use App\Http\Resources\Attachments\AttachmentResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentsController extends Controller
{
    public function __construct(
        private readonly UploadAttachmentHandler $uploadHandler,
        private readonly AttachmentStorageService $storage,
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

    public function content(AttachmentModel $attachment): StreamedResponse
    {
        return $this->storage->streamResponse($attachment);
    }
}
