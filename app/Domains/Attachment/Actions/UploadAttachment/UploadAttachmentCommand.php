<?php

namespace App\Domains\Attachment\Actions\UploadAttachment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

class UploadAttachmentCommand
{
    public function __construct(
        public readonly UploadedFile $file,
        public readonly ?Model $attachable = null,
        public readonly ?string $role = null,
    ) {}
}
