<?php

namespace App\Domains\Attachment\Actions\DeleteAttachment;

use App\Domains\Attachment\Services\AttachmentStorageService;

class DeleteAttachmentHandler
{
    public function __construct(
        private readonly AttachmentStorageService $storage,
    ) {}

    public function handle(DeleteAttachmentCommand $command): void
    {
        $this->storage->delete($command->attachment->storage_key);
        $command->attachment->delete();
    }
}
