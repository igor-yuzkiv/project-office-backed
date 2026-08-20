<?php

namespace App\Domains\Annotation\Policies;

use App\Domains\Annotation\Models\AnnotationModel;
use App\Domains\User\Models\UserModel;

class AnnotationPolicy
{
    public function viewAny(UserModel $user): bool
    {
        return true;
    }

    public function view(UserModel $user, AnnotationModel $annotation): bool
    {
        return true;
    }

    public function create(UserModel $user): bool
    {
        return true;
    }

    public function update(UserModel $user, AnnotationModel $annotation): bool
    {
        return $annotation->author_id === $user->id;
    }

    public function delete(UserModel $user, AnnotationModel $annotation): bool
    {
        return $annotation->author_id === $user->id;
    }
}
