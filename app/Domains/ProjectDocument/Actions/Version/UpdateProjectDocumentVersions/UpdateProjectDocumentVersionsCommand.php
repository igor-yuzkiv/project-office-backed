<?php

namespace App\Domains\ProjectDocument\Actions\Version\UpdateProjectDocumentVersions;

use App\Domains\ProjectDocument\Models\ProjectDocumentModel;

class UpdateProjectDocumentVersionsCommand
{
    /**
     * @param  array<int, array{id: string, content: string|null, label: string|null}>  $versions
     */
    public function __construct(
        public readonly ProjectDocumentModel $document,
        public readonly array $versions,
    ) {}
}
