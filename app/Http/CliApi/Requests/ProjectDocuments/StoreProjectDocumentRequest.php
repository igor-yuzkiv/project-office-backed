<?php

namespace App\Http\CliApi\Requests\ProjectDocuments;

use App\Domains\Project\Models\ProjectModel;
use App\Domains\ProjectDocument\Actions\CreateProjectDocument\CreateProjectDocumentCommand;
use App\Http\CliApi\Requests\Concerns\HasTagDtos;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectDocumentRequest extends FormRequest
{
    use HasTagDtos;

    public function rules(): array
    {
        /** @var ProjectModel $project */
        $project = $this->route('project');

        return [
            'title' => [
                'required',
                'string',
                'max:255',
                // CLI only creates root-level documents, so title must be unique among the project's roots.
                Rule::unique('project_documents')
                    ->where('project_id', $project->id)
                    ->whereNull('parent_id'),
            ],
            'content' => ['sometimes', 'nullable', 'string'],
            'tags'    => ['sometimes', 'nullable', 'string'],
        ];
    }

    public function toCommand(ProjectModel $project, ?array $tagIds = null): CreateProjectDocumentCommand
    {
        return new CreateProjectDocumentCommand(
            project: $project,
            title: $this->validated('title'),
            parentId: null,
            content: $this->validated('content'),
            tagIds: $tagIds,
        );
    }
}
