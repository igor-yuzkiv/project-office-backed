<?php

namespace App\Http\Controllers\Attachments;

use App\Domains\Attachment\Actions\DeleteAttachment\DeleteAttachmentHandler;
use App\Domains\Attachment\Models\AttachmentModel;
use App\Domains\Attachment\Services\AttachmentStorageService;
use App\Http\Controllers\ResourceController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class AttachmentsController extends ResourceController
{
    public function __construct(
        private readonly DeleteAttachmentHandler $deleteHandler,
        private readonly AttachmentStorageService $storage,
    ) {}

    protected function getAllowedIncludes(): array
    {
        return ['createdBy', 'updatedBy'];
    }

    public function destroy(AttachmentModel $attachment): JsonResponse
    {
        $this->deleteHandler->handle($attachment);

        return response()->json(['message' => 'Attachment deleted.']);
    }

    public function content(AttachmentModel $attachment): RedirectResponse
    {
        return redirect($this->storage->temporaryUrl($attachment));
    }
}
