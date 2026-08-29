<?php

namespace App\Domains\ProjectDocument\Actions\Version\UpdateProjectDocumentVersionContent;

use App\Domains\ProjectDocument\Models\ProjectDocumentVersionModel;

class UpdateProjectDocumentVersionContentCommand
{
    public function __construct(
        public readonly ProjectDocumentVersionModel $version,
        public readonly ?string $content,
    ) {}
}
