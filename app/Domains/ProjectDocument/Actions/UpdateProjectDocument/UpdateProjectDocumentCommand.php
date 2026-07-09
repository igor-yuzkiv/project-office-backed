<?php

namespace App\Domains\ProjectDocument\Actions\UpdateProjectDocument;

use App\Domains\ProjectDocument\Models\ProjectDocumentModel;

class UpdateProjectDocumentCommand
{
    /**
     * @param  array<string, mixed>  $attributes
     * @param  string[]|null  $tagIds
     */
    public function __construct(
        public readonly ProjectDocumentModel $document,
        public readonly array $attributes = [],
        public readonly ?array $tagIds = null,
    ) {}
}
