<?php

namespace App\Domains\ProjectDocument\Actions\CreateProjectDocument;

use App\Domains\Project\Models\ProjectModel;
use App\Domains\ProjectDocument\ValueObjects\ProjectDocumentKey;

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

    /**
     * @return array<string, mixed>
     */
    public function toModelAttributes(ProjectDocumentKey $documentKey): array
    {
        return [
            'project_id'      => $this->project->id,
            'parent_id'       => $this->parentId,
            'key'             => $documentKey->value,
            'sequence_number' => $documentKey->sequenceNumber,
            'title'           => $this->title,
            'content'         => $this->content,
        ];
    }
}
