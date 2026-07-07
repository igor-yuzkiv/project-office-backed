<?php

namespace App\Http\CliApi\Requests\Comment;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'comments'           => ['required', 'array', 'min:1'],
            'comments.*.content' => ['required', 'string', 'max:10000'],
        ];
    }
}
