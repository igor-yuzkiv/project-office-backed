<?php

namespace App\Domains\ProjectDocument\Actions\Version\DeleteProjectDocumentVersion;

use App\Domains\ProjectDocument\Models\ProjectDocumentVersionModel;

class DeleteProjectDocumentVersionCommand
{
    public function __construct(
        public readonly ProjectDocumentVersionModel $version,
    ) {}
}
