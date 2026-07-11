<?php

namespace App\Http\CliApi\Requests\ProjectDocuments;

use App\Domains\ProjectDocument\Actions\UpdateProjectDocument\UpdateProjectDocumentCommand;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Http\CliApi\Requests\Concerns\HasTagDtos;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectDocumentRequest extends FormRequest
{
    use HasTagDtos;

    public function rules(): array
    {
        /** @var ProjectDocumentModel $document */
        $document = $this->route('document');

        return [
            'title' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                // Sibling titles are unique per (project_id, parent_id); CLI never changes the parent.
                Rule::unique('project_documents')
                    ->where('project_id', $document->project_id)
                    ->where('parent_id', $document->parent_id)
                    ->ignore($document->id),
            ],
            'content' => ['sometimes', 'nullable', 'string'],
            'tags'    => ['sometimes', 'nullable', 'string'],
        ];
    }

    public function toCommand(ProjectDocumentModel $document, ?array $tagIds = null): UpdateProjectDocumentCommand
    {
        return new UpdateProjectDocumentCommand(
            document: $document,
            title: $this->has('title') ? $this->validated('title') : $document->title,
            content: $this->has('content') ? $this->validated('content') : $document->content,
            status: $document->status,
            tagIds: $tagIds,
        );
    }
}
