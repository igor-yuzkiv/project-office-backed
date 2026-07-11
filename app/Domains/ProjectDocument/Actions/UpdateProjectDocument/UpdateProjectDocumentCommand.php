<?php

namespace App\Domains\ProjectDocument\Actions\UpdateProjectDocument;

use App\Domains\ProjectDocument\Enums\ProjectDocumentStatus;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;

class UpdateProjectDocumentCommand
{
    /**
     * @param  string[]|null  $tagIds
     */
    public function __construct(
        public readonly ProjectDocumentModel $document,
        public readonly ?string $title = null,
        public readonly ?string $content = null,
        public readonly ?ProjectDocumentStatus $status = null,
        public readonly ?array $tagIds = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toModelAttributes(): array
    {
        return [
            'title'   => $this->title,
            'content' => $this->content,
            'status'  => $this->status?->value,
        ];
    }
}
