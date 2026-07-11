<?php

namespace App\Http\WebApi\Requests\ProjectDocuments;

use App\Domains\ProjectDocument\Actions\UpdateProjectDocument\UpdateProjectDocumentCommand;
use App\Domains\ProjectDocument\Enums\ProjectDocumentStatus;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectDocumentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title'     => ['sometimes', 'required', 'string', 'max:255'],
            'content'   => ['sometimes', 'nullable', 'string'],
            'status'    => ['sometimes', Rule::enum(ProjectDocumentStatus::class)],
            'tag_ids'   => ['sometimes', 'array'],
            'tag_ids.*' => ['string', 'exists:tags,id'],
        ];
    }

    public function toCommand(ProjectDocumentModel $projectDocument): UpdateProjectDocumentCommand
    {
        return new UpdateProjectDocumentCommand(
            document: $projectDocument,
            title: $this->has('title') ? $this->validated('title') : $projectDocument->title,
            content: $this->has('content') ? $this->validated('content') : $projectDocument->content,
            status: $this->has('status') ? ProjectDocumentStatus::from($this->validated('status')) : $projectDocument->status,
            tagIds: $this->validated('tag_ids'),
        );
    }
}
