<?php

namespace App\Domains\User\Actions\CreateApiToken;

use Laravel\Sanctum\NewAccessToken;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateApiTokenHandler
{
    use AsAction;

    public function handle(CreateApiTokenCommand $command): NewAccessToken
    {
        return $command->user->createToken($command->name, ['*'], $command->expiresAt);
    }
}
