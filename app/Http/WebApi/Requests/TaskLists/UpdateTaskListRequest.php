<?php

namespace App\Http\WebApi\Requests\TaskLists;

use App\Domains\TaskList\Actions\UpdateTaskList\UpdateTaskListCommand;
use App\Domains\TaskList\Enums\TaskListStatus;
use App\Domains\TaskList\Models\TaskListModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskListRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'status'      => ['required', 'string', Rule::enum(TaskListStatus::class)],
            'description' => ['nullable', 'string'],
            'tag_ids'     => ['nullable', 'array'],
            'tag_ids.*'   => ['string', 'exists:tags,id'],
        ];
    }

    public function toCommand(TaskListModel $taskList): UpdateTaskListCommand
    {
        return new UpdateTaskListCommand(
            taskList: $taskList,
            name: $this->validated('name'),
            status: TaskListStatus::from($this->validated('status')),
            description: $this->validated('description'),
            tagIds: $this->validated('tag_ids'),
        );
    }
}
