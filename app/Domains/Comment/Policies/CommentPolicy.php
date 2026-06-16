<?php

namespace App\Domains\Comment\Policies;

use App\Domains\Comment\Models\CommentModel;
use App\Domains\User\Models\UserModel;

class CommentPolicy
{
    public function viewAny(UserModel $user): bool
    {
        return true;
    }

    public function view(UserModel $user, CommentModel $comment): bool
    {
        return true;
    }

    public function create(UserModel $user): bool
    {
        return true;
    }

    public function update(UserModel $user, CommentModel $comment): bool
    {
        return $comment->author_id === $user->id;
    }

    public function delete(UserModel $user, CommentModel $comment): bool
    {
        return $comment->author_id === $user->id;
    }
}
