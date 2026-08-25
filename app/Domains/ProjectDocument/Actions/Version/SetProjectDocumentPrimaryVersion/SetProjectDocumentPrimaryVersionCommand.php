<?php

namespace App\Domains\ProjectDocument\Actions\Version\SetProjectDocumentPrimaryVersion;

use App\Domains\ProjectDocument\Models\ProjectDocumentModel;

class SetProjectDocumentPrimaryVersionCommand
{
    public function __construct(
        public readonly ProjectDocumentModel $document,
        /** Null returns the document to following its newest version. */
        public readonly ?string $versionId,
    ) {}
}
