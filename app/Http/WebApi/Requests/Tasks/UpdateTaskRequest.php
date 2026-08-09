<?php

namespace App\Http\WebApi\Requests\Tasks;

use App\Domains\Task\Actions\UpdateTask\UpdateTaskCommand;
use App\Domains\Task\Enums\TaskPriority;
use App\Domains\Task\Enums\TaskStatus;
use App\Domains\Task\Models\TaskModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'task_list_id' => ['nullable', 'string', 'ulid'],
            'name'         => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'priority'     => ['nullable', 'integer', Rule::enum(TaskPriority::class)],
            'status'       => ['required', 'string', Rule::enum(TaskStatus::class)],
            'start_date'   => ['nullable', 'date'],
            'due_date'     => ['nullable', 'date', 'after_or_equal:start_date'],
            'tag_ids'      => ['nullable', 'array'],
            'tag_ids.*'    => ['string', 'exists:tags,id'],
        ];
    }

    public function toCommand(TaskModel $task): UpdateTaskCommand
    {
        $priority = $this->validated('priority');
        $startDate = $this->validated('start_date');
        $dueDate = $this->validated('due_date');

        return new UpdateTaskCommand(
            task: $task,
            taskListId: $this->validated('task_list_id'),
            name: $this->validated('name'),
            description: $this->validated('description'),
            priority: $priority !== null ? TaskPriority::from((int) $priority) : TaskPriority::None,
            status: TaskStatus::from($this->validated('status')),
            startDate: $startDate ? Carbon::parse($startDate) : null,
            dueDate: $dueDate ? Carbon::parse($dueDate) : null,
            tagIds: $this->validated('tag_ids'),
        );
    }
}
