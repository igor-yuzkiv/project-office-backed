<?php

namespace App\Http\WebApi\Requests\Annotation;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnnotationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'content'          => ['required', 'string', 'max:5000'],
            'text_snapshot'    => ['nullable', 'string', 'max:500'],
            'anchor'           => ['required', 'array'],
            'anchor.version'   => ['required', 'integer', 'in:1'],
            'anchor.line'      => ['nullable', 'integer', 'min:0'],
            'anchor.tag'       => ['required', 'string', 'max:16'],
            'anchor.ordinal'   => ['required', 'integer', 'min:0'],
            'anchor.index'     => ['required', 'integer', 'min:0'],
            'anchor.text_hash' => ['required', 'string', 'max:64'],
        ];
    }
}
