<?php

namespace App\Http\WebApi\Requests\ProjectDocuments;

use App\Domains\ProjectDocument\Actions\SyncProjectDocumentTasks\SyncProjectDocumentTasksCommand;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SyncProjectDocumentTasksRequest extends FormRequest
{
    public function rules(): array
    {
        /** @var ProjectDocumentModel $projectDocument */
        $projectDocument = $this->route('project_document');

        return [
            'task_ids'   => ['present', 'array'],
            'task_ids.*' => [
                'string',
                'distinct',
                Rule::exists('tasks', 'id')->where('project_id', $projectDocument->project_id),
            ],
        ];
    }

    public function toCommand(ProjectDocumentModel $projectDocument): SyncProjectDocumentTasksCommand
    {
        return new SyncProjectDocumentTasksCommand(
            document: $projectDocument,
            taskIds: $this->validated('task_ids'),
        );
    }
}
