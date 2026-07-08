<?php

namespace App\Http\CliApi\Requests\Tasks;

use App\Domains\Task\Actions\UpdateTask\UpdateTaskCommand;
use App\Domains\Task\Enums\TaskStatus;
use App\Domains\Task\Models\TaskModel;
use App\Http\CliApi\Requests\Tasks\Concerns\HasTagDtos;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    use HasTagDtos;

    public function rules(): array
    {
        return [
            'name'        => ['sometimes', 'string', 'max:255'],
            'status'      => ['sometimes', 'string', Rule::enum(TaskStatus::class)],
            'description' => ['sometimes', 'nullable', 'string'],
            'tags'        => ['sometimes', 'nullable', 'string'],
        ];
    }

    public function toCommand(TaskModel $task, ?array $tagIds = null): UpdateTaskCommand
    {
        return new UpdateTaskCommand(
            task: $task,
            taskListId: $task->task_list_id,
            name: $this->has('name') ? $this->validated('name') : $task->name,
            description: $this->has('description') ? $this->validated('description') : $task->description,
            priority: $task->priority,
            status: $this->has('status') ? TaskStatus::from($this->validated('status')) : $task->status,
            startDate: $task->start_date,
            dueDate: $task->due_date,
            tagIds: $tagIds,
        );
    }
}
