<?php

namespace App\Domains\User\Actions\DeleteApiToken;

use Lorisleiva\Actions\Concerns\AsAction;

class DeleteApiTokenHandler
{
    use AsAction;

    public function handle(DeleteApiTokenCommand $command): void
    {
        $token = $command->user->tokens()->findOrFail($command->tokenId);
        $token->delete();
    }
}
