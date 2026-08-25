<?php

namespace App\Domains\ProjectDocument\Actions\Version\WriteProjectDocumentContent;

use App\Domains\ProjectDocument\Models\ProjectDocumentModel;

class WriteProjectDocumentContentCommand
{
    public function __construct(
        public readonly ProjectDocumentModel $document,
        public readonly ?string $content,
        public readonly ?string $authorId = null,
    ) {}
}
