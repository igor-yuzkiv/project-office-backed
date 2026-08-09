<?php

namespace App\Http\WebApi\Requests\TaskLists;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskListAttachmentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:25600'],
            'role' => ['nullable', 'string'],
        ];
    }
}
