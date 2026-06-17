<?php

namespace App\Http\Controllers\Tasks;

use App\Domains\Attachment\Actions\UploadAttachment\UploadAttachmentCommand;
use App\Domains\Attachment\Actions\UploadAttachment\UploadAttachmentHandler;
use App\Domains\Task\Models\TaskModel;
use App\Http\Requests\Tasks\StoreTaskAttachmentRequest;
use App\Http\Resources\Attachments\AttachmentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskAttachmentsController
{
    public function __construct(
        private readonly UploadAttachmentHandler $uploadHandler,
    ) {}

    public function index(TaskModel $task): AnonymousResourceCollection
    {
        $attachments = $task->attachments()
            ->with(['createdBy', 'updatedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return AttachmentResource::collection($attachments);
    }

    public function store(StoreTaskAttachmentRequest $request, TaskModel $task): JsonResponse
    {
        $command = new UploadAttachmentCommand(
            file: $request->file('file'),
            attachable: $task,
            role: $request->validated('role'),
        );

        $attachment = $this->uploadHandler->handle($command);
        $attachment->load(['createdBy', 'updatedBy']);

        return (new AttachmentResource($attachment))
            ->response()
            ->setStatusCode(201);
    }
}
