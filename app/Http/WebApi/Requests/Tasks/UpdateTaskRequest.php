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
            'task_list_id' => ['sometimes', 'nullable', 'string', 'ulid'],
            'name'         => ['sometimes', 'required', 'string', 'max:255'],
            'description'  => ['sometimes', 'nullable', 'string'],
            'priority'     => ['sometimes', 'nullable', 'integer', Rule::enum(TaskPriority::class)],
            'status'       => ['sometimes', 'string', Rule::enum(TaskStatus::class)],
            'start_date'   => ['sometimes', 'nullable', 'date'],
            'due_date'     => ['sometimes', 'nullable', 'date', 'after_or_equal:start_date'],
            'tag_ids'      => ['sometimes', 'array'],
            'tag_ids.*'    => ['string', 'exists:tags,id'],
        ];
    }

    public function toCommand(TaskModel $task): UpdateTaskCommand
    {
        $validated = $this->validated();
        $priority = match (true) {
            !array_key_exists('priority', $validated) => $task->priority ?? TaskPriority::None,
            $validated['priority'] === null           => TaskPriority::None,
            default                                   => TaskPriority::from((int) $validated['priority']),
        };

        $startDate = $this->validated('start_date');
        $dueDate = $this->validated('due_date');

        return new UpdateTaskCommand(
            task: $task,
            taskListId: $this->validated('task_list_id'),
            name: $this->validated('name'),
            description: $this->validated('description'),
            priority: $priority,
            status: ($s = $this->validated('status')) !== null ? TaskStatus::from($s) : null,
            startDate: $startDate ? Carbon::parse($startDate) : null,
            dueDate: $dueDate ? Carbon::parse($dueDate) : null,
            tagIds: $this->validated('tag_ids'),
        );
    }
}
