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
            'name'        => ['sometimes', 'required', 'string', 'max:255'],
            'status'      => ['sometimes', 'string', Rule::enum(TaskListStatus::class)],
            'description' => ['sometimes', 'nullable', 'string'],
            'tag_ids'     => ['sometimes', 'array'],
            'tag_ids.*'   => ['string', 'exists:tags,id'],
        ];
    }

    public function toCommand(TaskListModel $taskList): UpdateTaskListCommand
    {
        $status = $this->validated('status');

        return new UpdateTaskListCommand(
            taskList: $taskList,
            name: $this->validated('name'),
            status: $status !== null ? TaskListStatus::from($status) : null,
            description: $this->validated('description'),
            descriptionProvided: array_key_exists('description', $this->validated()),
            tagIds: $this->validated('tag_ids'),
        );
    }
}
