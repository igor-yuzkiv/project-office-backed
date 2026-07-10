<?php

namespace App\Http\WebApi\Controllers\Users;

use App\Domains\User\Actions\UpdateUserAvatar\UpdateUserAvatarCommand;
use App\Domains\User\Actions\UpdateUserAvatar\UpdateUserAvatarHandler;
use App\Domains\User\Models\UserModel;
use App\Http\Shared\Resources\Users\UserResource;
use App\Http\WebApi\Requests\Users\StoreUserAvatarRequest;
use Illuminate\Support\Facades\Auth;

class UserAvatarController
{
    public function __construct(
        private readonly UpdateUserAvatarHandler $updateHandler,
    ) {}

    public function store(StoreUserAvatarRequest $request): UserResource
    {
        $user = $this->updateHandler->handle(new UpdateUserAvatarCommand(
            user: $this->authenticatedUser(),
            file: $request->file('avatar'),
        ));

        return new UserResource($user);
    }

    private function authenticatedUser(): UserModel
    {
        $user = Auth::user();

        abort_unless($user instanceof UserModel, 401);

        return $user;
    }
}
