<?php

namespace App\Http\Controllers\Attachments;

use App\Domains\Attachment\Actions\DeleteAttachment\DeleteAttachmentHandler;
use App\Domains\Attachment\Models\AttachmentModel;
use App\Domains\Attachment\Services\AttachmentStorageService;
use App\Http\Controllers\ResourceController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        return redirect($this->storage->temporaryUrl($attachment->storage_key));
    }

    public function download(AttachmentModel $attachment): StreamedResponse
    {
        return $this->storage->streamResponse($attachment->storage_key, $attachment->original_name);
    }

    public function temporaryUrl(AttachmentModel $attachment): JsonResponse
    {
        return response()->json(['url' => $this->storage->temporaryUrl($attachment->storage_key)]);
    }
}
