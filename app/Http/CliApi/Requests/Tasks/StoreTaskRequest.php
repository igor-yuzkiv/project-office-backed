<?php

namespace App\Http\CliApi\Requests\Tasks;

use App\Domains\Project\Models\ProjectModel;
use App\Domains\Task\Actions\CreateTask\CreateTaskCommand;
use App\Domains\Task\Enums\TaskPriority;
use App\Http\CliApi\Requests\Concerns\HasTagDtos;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    use HasTagDtos;

    public function rules(): array
    {
        return [
            'task_list_id' => ['nullable', 'string', 'ulid'],
            'name'         => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'priority'     => ['nullable', 'integer', Rule::enum(TaskPriority::class)],
            'start_date'   => ['sometimes', 'nullable', 'date'],
            'due_date'     => ['sometimes', 'nullable', 'date', 'after_or_equal:start_date'],
            'tags'         => ['sometimes', 'nullable', 'string'],
        ];
    }

    public function toCommand(ProjectModel $project, ?array $tagIds = null): CreateTaskCommand
    {
        $rawPriority = $this->validated('priority');
        $startDate = $this->validated('start_date');
        $dueDate = $this->validated('due_date');

        return new CreateTaskCommand(
            projectId: $project->id,
            name: $this->validated('name'),
            priority: $rawPriority !== null ? TaskPriority::from((int) $rawPriority) : TaskPriority::None,
            taskListId: $this->validated('task_list_id'),
            description: $this->validated('description'),
            startDate: $startDate ? Carbon::parse($startDate) : null,
            dueDate: $dueDate ? Carbon::parse($dueDate) : null,
            tagIds: $tagIds,
        );
    }
}
