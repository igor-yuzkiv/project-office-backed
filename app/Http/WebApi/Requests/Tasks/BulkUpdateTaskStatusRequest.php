<?php

namespace App\Http\WebApi\Requests\Tasks;

use App\Domains\Task\Actions\BulkUpdateTaskStatus\BulkUpdateTaskStatusCommand;
use App\Domains\Task\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkUpdateTaskStatusRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'task_ids'   => ['required', 'array', 'min:1', 'max:100'],
            'task_ids.*' => ['string', 'ulid', 'exists:tasks,id'],
            'status'     => ['required', 'string', Rule::enum(TaskStatus::class)],
        ];
    }

    public function toCommand(): BulkUpdateTaskStatusCommand
    {
        return new BulkUpdateTaskStatusCommand(
            taskIds: $this->validated('task_ids'),
            status: TaskStatus::from($this->validated('status')),
        );
    }
}
