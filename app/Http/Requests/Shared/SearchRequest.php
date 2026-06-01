<?php

namespace App\Http\Requests\Shared;

use App\Libs\EloquentFilters\MatchMode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'query'                => ['sometimes', 'nullable', 'string', 'max:255'],
            'filters'              => ['sometimes', 'array'],
            'filters.*.filter_key' => ['required', 'string'],
            'filters.*.field_name' => ['required', 'string'],
            'filters.*.value'      => ['nullable'],
            'filters.*.matchMode'  => ['nullable', Rule::enum(MatchMode::class)],
            'filters.*.params'     => ['sometimes', 'array'],
            'page'                 => ['sometimes', 'integer', 'min:1'],
            'per_page'             => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort_by'              => ['sometimes', 'string'],
            'sort_order'           => ['sometimes', 'string', 'in:asc,desc'],
        ];
    }
}
