<?php

namespace App\Http\Requests\Tasks;

use App\Domains\Task\Enums\TaskPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'task_list_id' => ['nullable', 'string', 'ulid'],
            'name'         => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'priority'     => ['required', 'integer', Rule::in(array_column(TaskPriority::cases(), 'value'))],
        ];
    }
}
