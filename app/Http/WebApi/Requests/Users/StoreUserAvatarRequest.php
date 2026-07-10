<?php

namespace App\Http\WebApi\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserAvatarRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'avatar' => ['required', 'file', 'mimes:jpg,jpeg,png,gif', 'max:5120'],
        ];
    }
}
