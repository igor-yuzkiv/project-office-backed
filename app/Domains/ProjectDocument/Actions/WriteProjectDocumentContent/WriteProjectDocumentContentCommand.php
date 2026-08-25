<?php

namespace App\Domains\ProjectDocument\Actions\WriteProjectDocumentContent;

use App\Domains\ProjectDocument\Models\ProjectDocumentModel;

class WriteProjectDocumentContentCommand
{
    public function __construct(
        public readonly ProjectDocumentModel $document,
        public readonly ?string $content,
        public readonly ?string $authorId = null,
        /**
         * Writing the content of a document that is being created in the same request is part of
         * the creation, not an update on top of it; that path passes false.
         */
        public readonly bool $recordsUpdate = true,
    ) {}
}
