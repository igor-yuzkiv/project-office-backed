<?php

namespace App\Http\WebApi\Requests\Attachments;

use Illuminate\Foundation\Http\FormRequest;

class UploadAttachmentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'file'        => ['required', 'file', 'max:25600'],
            'entity_type' => ['nullable', 'string'],
            'entity_id'   => ['nullable', 'string'],
            'role'        => ['nullable', 'string'],
        ];
    }
}
