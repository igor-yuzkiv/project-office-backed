<?php

namespace App\Http\CliApi\Requests\TaskLists;

use App\Domains\TaskList\Actions\UpdateTaskList\UpdateTaskListCommand;
use App\Domains\TaskList\Enums\TaskListStatus;
use App\Domains\TaskList\Models\TaskListModel;
use App\Http\CliApi\Requests\Concerns\HasTagDtos;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskListRequest extends FormRequest
{
    use HasTagDtos;

    public function rules(): array
    {
        return [
            'name'        => ['sometimes', 'string', 'max:255'],
            'status'      => ['sometimes', 'string', Rule::enum(TaskListStatus::class)],
            'description' => ['sometimes', 'nullable', 'string'],
            'tags'        => ['sometimes', 'nullable', 'string'],
        ];
    }

    /**
     * The CLI sends only the fields it wants changed, so absent fields fall back to the
     * current values before reaching the full-object update handler.
     */
    public function toCommand(TaskListModel $taskList, ?array $tagIds = null): UpdateTaskListCommand
    {
        return new UpdateTaskListCommand(
            taskList: $taskList,
            name: $this->has('name') ? $this->validated('name') : $taskList->name,
            status: $this->has('status') ? TaskListStatus::from($this->validated('status')) : $taskList->status,
            description: $this->has('description') ? $this->validated('description') : $taskList->description,
            tagIds: $tagIds,
        );
    }
}
