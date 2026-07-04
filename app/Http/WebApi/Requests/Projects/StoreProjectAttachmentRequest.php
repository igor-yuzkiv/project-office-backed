<?php

namespace App\Http\WebApi\Requests\Projects;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectAttachmentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:25600'],
            'role' => ['nullable', 'string'],
        ];
    }
}
