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

        return [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('project_documents')
                    ->where('project_id', $project->id)
                    ->whereNull('parent_id'),
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
            tagIds: $this->validated('tag_ids'),
        );
    }
}
