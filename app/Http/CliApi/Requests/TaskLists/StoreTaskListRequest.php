<?php

namespace App\Http\CliApi\Requests\TaskLists;

use App\Domains\Project\Models\ProjectModel;
use App\Domains\TaskList\Actions\CreateTaskList\CreateTaskListCommand;
use App\Domains\TaskList\Enums\TaskListStatus;
use App\Http\CliApi\Requests\Concerns\HasTagDtos;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskListRequest extends FormRequest
{
    use HasTagDtos;

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'status'      => ['sometimes', 'string', Rule::enum(TaskListStatus::class)],
            'description' => ['nullable', 'string'],
            'tags'        => ['sometimes', 'nullable', 'string'],
        ];
    }

    public function toCommand(ProjectModel $project, ?array $tagIds = null): CreateTaskListCommand
    {
        $status = $this->validated('status');

        return new CreateTaskListCommand(
            projectId: $project->id,
            name: $this->validated('name'),
            status: $status !== null ? TaskListStatus::from($status) : TaskListStatus::Open,
            description: $this->validated('description'),
            tagIds: $tagIds,
        );
    }
}
