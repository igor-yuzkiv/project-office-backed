<?php

namespace App\Domains\Attachment\Actions\UploadAttachment;

use App\Domains\Shared\ValueObjects\EntityRef;
use Illuminate\Http\UploadedFile;

class UploadAttachmentCommand
{
    public function __construct(
        public readonly UploadedFile $file,
        public readonly ?EntityRef $entityRef = null,
        public readonly ?string $role = null,
    ) {}
}
