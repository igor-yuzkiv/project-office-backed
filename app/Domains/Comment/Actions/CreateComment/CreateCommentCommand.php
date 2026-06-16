<?php

namespace App\Domains\Comment\Actions\CreateComment;

use App\Domains\Task\Models\TaskModel;
use App\Domains\User\Models\UserModel;

class CreateCommentCommand
{
    public function __construct(
        public readonly TaskModel $task,
        public readonly UserModel $author,
        public readonly string $content,
    ) {}
}
