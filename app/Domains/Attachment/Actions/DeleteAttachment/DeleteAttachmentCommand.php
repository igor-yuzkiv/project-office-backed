<?php

namespace App\Domains\Attachment\Actions\DeleteAttachment;

use App\Domains\Attachment\Models\AttachmentModel;

class DeleteAttachmentCommand
{
    public function __construct(
        public readonly AttachmentModel $attachment,
    ) {}
}
