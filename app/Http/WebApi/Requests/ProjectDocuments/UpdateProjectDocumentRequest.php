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
            'title'     => ['required', 'string', 'max:255'],
            'content'   => ['nullable', 'string'],
            'status'    => ['required', Rule::enum(ProjectDocumentStatus::class)],
            'tag_ids'   => ['nullable', 'array'],
            'tag_ids.*' => ['string', 'exists:tags,id'],
        ];
    }

    public function toCommand(ProjectDocumentModel $projectDocument): UpdateProjectDocumentCommand
    {
        return new UpdateProjectDocumentCommand(
            document: $projectDocument,
            title: $this->validated('title'),
            content: $this->validated('content'),
            status: ProjectDocumentStatus::from($this->validated('status')),
            tagIds: $this->validated('tag_ids'),
        );
    }
}
