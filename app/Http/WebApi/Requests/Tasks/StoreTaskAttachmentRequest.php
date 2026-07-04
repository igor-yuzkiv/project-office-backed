<?php

namespace App\Http\WebApi\Requests\Tasks;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskAttachmentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:25600'],
            'role' => ['nullable', 'string'],
        ];
    }
}
