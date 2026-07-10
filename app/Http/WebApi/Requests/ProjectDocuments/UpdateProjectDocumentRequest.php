<?php

namespace App\Http\WebApi\Requests\ProjectDocuments;

use App\Domains\ProjectDocument\Actions\UpdateProjectDocument\UpdateProjectDocumentCommand;
use App\Domains\ProjectDocument\Enums\ProjectDocumentStatus;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class UpdateProjectDocumentRequest extends FormRequest
{
    public function rules(): array
    {
        /** @var ProjectDocumentModel $document */
        $document = $this->route('project_document');

        return [
            'title'     => ['sometimes', 'required', 'string', 'max:255'],
            'content'   => ['sometimes', 'nullable', 'string'],
            'status'    => ['sometimes', Rule::enum(ProjectDocumentStatus::class)],
            'parent_id' => [
                'sometimes',
                'nullable',
                'string',
                'ulid',
                Rule::exists('project_documents', 'id')->where('project_id', $document->project_id),
            ],
            'tag_ids'   => ['sometimes', 'array'],
            'tag_ids.*' => ['string', 'exists:tags,id'],
        ];
    }

    public function toCommand(ProjectDocumentModel $projectDocument): UpdateProjectDocumentCommand
    {
        return new UpdateProjectDocumentCommand(
            document: $projectDocument,
            attributes: Arr::only($this->validated(), ['title', 'content', 'status', 'parent_id']),
            tagIds: $this->validated('tag_ids'),
        );
    }
}
