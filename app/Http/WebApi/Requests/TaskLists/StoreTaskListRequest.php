<?php

namespace App\Http\WebApi\Requests\TaskLists;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskListRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'project_id' => ['required', 'string', 'ulid'],
            'name'       => ['required', 'string', 'max:255'],
        ];
    }
}
