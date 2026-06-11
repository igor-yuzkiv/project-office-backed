<?php

namespace App\Http\Requests\Projects;

use App\Domains\Project\Enums\ProjectStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'   => ['sometimes', 'required', 'string', 'max:255'],
            'prefix' => ['sometimes', 'string', 'max:5'],
            'status' => ['sometimes', Rule::enum(ProjectStatus::class)],
        ];
    }
}
