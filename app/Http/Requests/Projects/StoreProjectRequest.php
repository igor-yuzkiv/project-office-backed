<?php

namespace App\Http\Requests\Projects;

use App\Domains\Project\Enums\ProjectStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:255', 'min:3'],
            'prefix'    => ['sometimes', 'string', 'max:5'],
            'status'    => ['sometimes', Rule::enum(ProjectStatus::class)],
            'tag_ids'   => ['sometimes', 'array'],
            'tag_ids.*' => ['string', 'exists:tags,id'],
        ];
    }
}
