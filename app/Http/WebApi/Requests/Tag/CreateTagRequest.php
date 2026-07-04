<?php

namespace App\Http\WebApi\Requests\Tag;

use Illuminate\Foundation\Http\FormRequest;

class CreateTagRequest extends FormRequest
{
    public function prepareForValidation(): void
    {
        if ($this->has('name')) {
            $this->merge(['name' => strtolower(trim((string) $this->input('name')))]);
        }
    }

    public function rules(): array
    {
        return [
            'name'  => ['required', 'string', 'max:64', 'unique:tags,name'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Tag with this name already exists.',
        ];
    }
}
