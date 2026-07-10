<?php

namespace App\Http\WebApi\Requests\Tasks;

use App\Domains\Task\Actions\SyncTaskProjectDocuments\SyncTaskProjectDocumentsCommand;
use App\Domains\Task\Models\TaskModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SyncTaskProjectDocumentsRequest extends FormRequest
{
    public function rules(): array
    {
        /** @var TaskModel $task */
        $task = $this->route('task');

        return [
            'document_ids'   => ['present', 'array'],
            'document_ids.*' => [
                'string',
                'distinct',
                Rule::exists('project_documents', 'id')->where('project_id', $task->project_id),
            ],
        ];
    }

    public function toCommand(TaskModel $task): SyncTaskProjectDocumentsCommand
    {
        return new SyncTaskProjectDocumentsCommand(
            task: $task,
            documentIds: $this->validated('document_ids'),
        );
    }
}
