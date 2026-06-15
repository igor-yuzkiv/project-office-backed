<?php

namespace App\Http\Requests\Tasks;

use App\Domains\Task\Actions\CreateTask\CreateTaskCommand;
use App\Domains\Task\Enums\TaskPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'project_id'   => ['required', 'string', 'ulid'],
            'task_list_id' => ['nullable', 'string', 'ulid'],
            'name'         => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'priority'     => ['nullable', 'integer', Rule::enum(TaskPriority::class)],
            'start_date'   => ['sometimes', 'nullable', 'date'],
            'due_date'     => ['sometimes', 'nullable', 'date', 'after_or_equal:start_date'],
            'tag_ids'      => ['sometimes', 'array'],
            'tag_ids.*'    => ['string', 'exists:tags,id'],
        ];
    }

    public function toCommand(): CreateTaskCommand
    {
        $rawPriority = $this->validated('priority');
        $startDate = $this->validated('start_date');
        $dueDate = $this->validated('due_date');

        return new CreateTaskCommand(
            projectId: $this->validated('project_id'),
            name: $this->validated('name'),
            priority: $rawPriority !== null ? TaskPriority::from((int) $rawPriority) : TaskPriority::None,
            taskListId: $this->validated('task_list_id'),
            description: $this->validated('description'),
            startDate: $startDate ? Carbon::parse($startDate) : null,
            dueDate: $dueDate ? Carbon::parse($dueDate) : null,
            tagIds: $this->validated('tag_ids'),
        );
    }
}
