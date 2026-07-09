<?php

namespace App\Http\WebApi\Requests\ProjectDocuments;

use App\Domains\Project\Models\ProjectModel;
use App\Domains\ProjectDocument\Actions\CreateProjectDocument\CreateProjectDocumentCommand;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectDocumentRequest extends FormRequest
{
    public function rules(): array
    {
        /** @var ProjectModel $project */
        $project = $this->route('project');
        $parentId = $this->input('parent_id');

        return [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('project_documents')
                    ->where('project_id', $project->id)
                    ->where(fn ($query) => $parentId === null
                        ? $query->whereNull('parent_id')
                        : $query->where('parent_id', $parentId)),
            ],
            'parent_id' => [
                'nullable',
                'string',
                'ulid',
                Rule::exists('project_documents', 'id')->where('project_id', $project->id),
            ],
            'tag_ids'   => ['sometimes', 'array'],
            'tag_ids.*' => ['string', 'exists:tags,id'],
        ];
    }

    public function toCommand(ProjectModel $project): CreateProjectDocumentCommand
    {
        return new CreateProjectDocumentCommand(
            projectId: $project->id,
            title: $this->validated('title'),
            parentId: $this->validated('parent_id'),
            tagIds: $this->validated('tag_ids'),
        );
    }
}
