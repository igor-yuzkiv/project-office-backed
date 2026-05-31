<?php

namespace App\Http\Requests\Projects;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'   => ['required', 'string', 'max:255', 'min:3'],
            'prefix' => ['sometimes', 'string', 'max:5'],
        ];
    }
}
