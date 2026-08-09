<?php

namespace App\Http\WebApi\Requests\TaskLists;

use App\Domains\TaskList\Actions\CreateTaskList\CreateTaskListCommand;
use App\Domains\TaskList\Enums\TaskListStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskListRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'project_id'  => ['required', 'string', 'ulid'],
            'name'        => ['required', 'string', 'max:255'],
            'status'      => ['sometimes', 'string', Rule::enum(TaskListStatus::class)],
            'description' => ['nullable', 'string'],
            'tag_ids'     => ['sometimes', 'array'],
            'tag_ids.*'   => ['string', 'exists:tags,id'],
        ];
    }

    public function toCommand(): CreateTaskListCommand
    {
        $status = $this->validated('status');

        return new CreateTaskListCommand(
            projectId: $this->validated('project_id'),
            name: $this->validated('name'),
            status: $status !== null ? TaskListStatus::from($status) : TaskListStatus::Open,
            description: $this->validated('description'),
            tagIds: $this->validated('tag_ids'),
        );
    }
}
