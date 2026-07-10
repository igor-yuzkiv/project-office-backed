<?php

namespace App\Domains\ProjectDocument\Actions\DeleteProjectDocument;

use App\Domains\ProjectDocument\Models\ProjectDocumentModel;

class DeleteProjectDocumentCommand
{
    public function __construct(
        public readonly ProjectDocumentModel $document,
    ) {}
}
