<?php

namespace App\Http\WebApi\Controllers\TaskLists;

use App\Domains\Attachment\Actions\UploadAttachment\UploadAttachmentCommand;
use App\Domains\Attachment\Actions\UploadAttachment\UploadAttachmentHandler;
use App\Domains\TaskList\Models\TaskListModel;
use App\Http\Shared\Resources\Attachments\AttachmentResource;
use App\Http\WebApi\Requests\TaskLists\StoreTaskListAttachmentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskListAttachmentsController
{
    public function __construct(
        private readonly UploadAttachmentHandler $uploadHandler,
    ) {}

    public function index(TaskListModel $taskList): AnonymousResourceCollection
    {
        $attachments = $taskList->attachments()
            ->with(['createdBy', 'updatedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return AttachmentResource::collection($attachments);
    }

    public function store(StoreTaskListAttachmentRequest $request, TaskListModel $taskList): JsonResponse
    {
        $command = new UploadAttachmentCommand(
            file: $request->file('file'),
            attachable: $taskList,
            role: $request->validated('role'),
        );

        $attachment = $this->uploadHandler->handle($command);
        $attachment->load(['createdBy', 'updatedBy']);

        return (new AttachmentResource($attachment))
            ->response()
            ->setStatusCode(201);
    }
}
