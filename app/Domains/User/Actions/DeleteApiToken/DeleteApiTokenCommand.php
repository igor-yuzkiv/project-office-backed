<?php

namespace App\Domains\User\Actions\DeleteApiToken;

use App\Domains\User\Models\UserModel;

class DeleteApiTokenCommand
{
    public function __construct(
        public readonly UserModel $user,
        public readonly int $tokenId,
    ) {}
}
