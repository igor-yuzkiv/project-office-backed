<?php

namespace App\Http\WebApi\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class StoreApiTokenRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:255'],
            'expires_at' => ['required', 'date', 'after_or_equal:today'],
        ];
    }
}
