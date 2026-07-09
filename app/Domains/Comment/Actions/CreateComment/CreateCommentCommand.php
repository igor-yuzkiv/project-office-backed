<?php

namespace App\Domains\Comment\Actions\CreateComment;

use App\Domains\User\Models\UserModel;
use App\Infrastructure\Models\Contracts\Commentable;

class CreateCommentCommand
{
    public function __construct(
        public readonly Commentable $commentable,
        public readonly UserModel $author,
        public readonly string $content,
    ) {}
}
