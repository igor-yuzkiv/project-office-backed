<?php

namespace App\Http\CliApi\Controllers\Users;

use App\Http\Shared\Resources\Users\UserResource;
use Illuminate\Http\Request;

class UserController
{
    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }
}
