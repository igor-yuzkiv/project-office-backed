<?php

namespace App\Domains\ProjectDocument\Actions\CreateProjectDocument;

use App\Domains\Project\Models\ProjectModel;

class CreateProjectDocumentCommand
{
    /**
     * @param  string[]|null  $tagIds
     */
    public function __construct(
        public readonly ProjectModel $project,
        public readonly string $title,
        public readonly ?string $parentId = null,
        public readonly ?string $content = null,
        public readonly ?array $tagIds = null,
    ) {}
}
