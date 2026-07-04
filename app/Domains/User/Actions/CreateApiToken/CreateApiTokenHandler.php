<?php

namespace App\Domains\User\Actions\CreateApiToken;

use Laravel\Sanctum\NewAccessToken;

class CreateApiTokenHandler
{
    public function handle(CreateApiTokenCommand $command): NewAccessToken
    {
        return $command->user->createToken($command->name, ['*'], $command->expiresAt);
    }
}
