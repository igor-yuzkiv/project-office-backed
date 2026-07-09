<?php

namespace App\Domains\ProjectDocument\Actions\CreateProjectDocument;

class CreateProjectDocumentCommand
{
    /**
     * @param  string[]|null  $tagIds
     */
    public function __construct(
        public readonly string $projectId,
        public readonly string $title,
        public readonly ?array $tagIds = null,
    ) {}
}
