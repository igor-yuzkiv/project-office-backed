<?php

namespace App\Domains\User\Actions\UpdateUserAvatar;

use App\Domains\User\Models\UserModel;
use Illuminate\Http\UploadedFile;

class UpdateUserAvatarCommand
{
    public function __construct(
        public readonly UserModel $user,
        public readonly UploadedFile $file,
    ) {}
}
