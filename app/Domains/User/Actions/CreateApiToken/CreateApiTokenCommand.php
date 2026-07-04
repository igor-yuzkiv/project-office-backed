<?php

namespace App\Domains\User\Actions\CreateApiToken;

use App\Domains\User\Models\UserModel;
use Illuminate\Support\Carbon;

class CreateApiTokenCommand
{
    public function __construct(
        public readonly UserModel $user,
        public readonly string $name,
        public readonly Carbon $expiresAt,
    ) {}
}
