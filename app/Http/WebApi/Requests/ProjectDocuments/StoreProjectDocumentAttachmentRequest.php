<?php

namespace App\Http\WebApi\Requests\ProjectDocuments;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectDocumentAttachmentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:25600'],
            'role' => ['nullable', 'string'],
        ];
    }
}
