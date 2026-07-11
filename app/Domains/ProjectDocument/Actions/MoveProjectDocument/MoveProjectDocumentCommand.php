<?php

namespace App\Domains\ProjectDocument\Actions\MoveProjectDocument;

use App\Domains\ProjectDocument\Models\ProjectDocumentModel;

class MoveProjectDocumentCommand
{
    public function __construct(
        public readonly ProjectDocumentModel $document,
        public readonly ?string $parentId,
    ) {}
}
