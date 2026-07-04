<?php

namespace App\Domains\User\Actions\DeleteApiToken;

class DeleteApiTokenHandler
{
    public function handle(DeleteApiTokenCommand $command): void
    {
        $token = $command->user->tokens()->findOrFail($command->tokenId);
        $token->delete();
    }
}
