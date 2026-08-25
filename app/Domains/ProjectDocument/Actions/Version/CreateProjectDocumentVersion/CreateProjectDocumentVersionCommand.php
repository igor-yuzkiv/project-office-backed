<?php

namespace App\Domains\ProjectDocument\Actions\Version\CreateProjectDocumentVersion;

use App\Domains\ProjectDocument\Models\ProjectDocumentModel;

class CreateProjectDocumentVersionCommand
{
    public function __construct(
        public readonly ProjectDocumentModel $document,
        public readonly ?string $label = null,
        public readonly ?string $copyContentFromVersionId = null,
        public readonly ?string $authorId = null,
    ) {}
}
