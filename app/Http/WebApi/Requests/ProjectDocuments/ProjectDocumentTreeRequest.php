<?php

namespace App\Http\WebApi\Requests\ProjectDocuments;

use Illuminate\Foundation\Http\FormRequest;

class ProjectDocumentTreeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'parent_id' => ['nullable', 'string', 'ulid', 'exists:project_documents,id'],
            'page'      => ['nullable', 'integer', 'min:1'],
            'per_page'  => ['nullable', 'integer', 'min:1', 'max:100'],
            'filters'   => ['nullable', 'array'],
        ];
    }
}
