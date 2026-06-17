<?php

namespace App\Domains\Attachment\Actions\DeleteAttachment;

use App\Domains\Attachment\Models\AttachmentModel;
use App\Domains\Attachment\Services\AttachmentStorageService;

class DeleteAttachmentHandler
{
    public function __construct(
        private readonly AttachmentStorageService $storage,
    ) {}

    public function handle(AttachmentModel $attachment): void
    {
        $this->storage->delete($attachment->storage_key);
        $attachment->delete();
    }
}
