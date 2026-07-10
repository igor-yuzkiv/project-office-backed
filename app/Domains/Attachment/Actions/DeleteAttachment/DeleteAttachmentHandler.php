<?php

namespace App\Domains\Attachment\Actions\DeleteAttachment;

use App\Domains\Attachment\Services\AttachmentStorageService;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteAttachmentHandler
{
    use AsAction;

    public function __construct(
        private readonly AttachmentStorageService $storage,
    ) {}

    public function handle(DeleteAttachmentCommand $command): void
    {
        $this->storage->delete($command->attachment->storage_key);
        $command->attachment->delete();
    }
}
