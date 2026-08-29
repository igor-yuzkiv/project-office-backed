<?php

namespace App\Domains\ProjectDocument\Actions\Version\UpdateProjectDocumentVersion;

use App\Domains\ProjectDocument\Models\ProjectDocumentVersionModel;

/** The version's own fields, as opposed to its content, which is written on its own. */
class UpdateProjectDocumentVersionCommand
{
    public function __construct(
        public readonly ProjectDocumentVersionModel $version,
        public readonly ?string $label,
    ) {}
}
