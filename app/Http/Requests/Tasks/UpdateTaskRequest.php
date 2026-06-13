<?php

namespace App\Http\Requests\Tasks;

use App\Domains\Task\Enums\TaskPriority;
use App\Domains\Task\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
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
            'tag_ids'      => ['sometimes', 'array'],
            'tag_ids.*'    => ['string', 'exists:tags,id'],
        ];
    }
}
