<?php

namespace App\Domains\ProjectDocument\Actions\Document\CreateProjectDocument;

use App\Domains\Project\Models\ProjectModel;
use App\Domains\ProjectDocument\Enums\ProjectDocumentStatus;
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
        public readonly ?array $tagIds = null,
        // Optional so the CLI, which builds this same command, keeps its contract; null leaves
        // the status to the column default.
        public readonly ?ProjectDocumentStatus $status = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toModelAttributes(ProjectDocumentKey $documentKey): array
    {
        $attributes = [
            'project_id'      => $this->project->id,
            'parent_id'       => $this->parentId,
            'key'             => $documentKey->value,
            'sequence_number' => $documentKey->sequenceNumber,
            'title'           => $this->title,
        ];

        if ($this->status !== null) {
            $attributes['status'] = $this->status;
        }

        return $attributes;
    }
}
