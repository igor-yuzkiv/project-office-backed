<?php

namespace App\Http\Controllers\Attachments;

use App\Domains\Attachment\Actions\DeleteAttachment\DeleteAttachmentHandler;
use App\Domains\Attachment\Actions\UploadAttachment\UploadAttachmentCommand;
use App\Domains\Attachment\Actions\UploadAttachment\UploadAttachmentHandler;
use App\Domains\Attachment\Models\AttachmentModel;
use App\Domains\Attachment\Services\AttachmentStorageService;
use App\Domains\Shared\ValueObjects\EntityRef;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attachments\UploadAttachmentRequest;
use App\Http\Requests\Shared\SearchRequest;
use App\Http\Resources\Attachments\AttachmentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AttachmentsController extends Controller
{
    public function __construct(
        private readonly UploadAttachmentHandler $uploadHandler,
        private readonly DeleteAttachmentHandler $deleteHandler,
        private readonly AttachmentStorageService $storage,
    ) {}

    public function search(SearchRequest $request): AnonymousResourceCollection
    {
        $sort = $this->getSortParams();
        $pagination = $this->getPaginationParams();

        $attachments = AttachmentModel::query()
            ->with(['createdBy', 'updatedBy'])
            ->filter((array) $request->input('filters', []))
            ->orderBy($sort->field, $sort->direction)
            ->paginate($pagination->perPage, page: $pagination->page);

        return AttachmentResource::collection($attachments);
    }

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

    public function destroy(AttachmentModel $attachment): JsonResponse
    {
        $this->deleteHandler->handle($attachment);

        return response()->json(['message' => 'Attachment deleted.']);
    }

    public function content(AttachmentModel $attachment): RedirectResponse
    {
        return redirect()->away($this->storage->temporaryUrl($attachment));
    }
}
