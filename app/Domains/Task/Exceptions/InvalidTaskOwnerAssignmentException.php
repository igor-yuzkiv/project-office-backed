<?php

namespace App\Domains\Task\Exceptions;

use RuntimeException;

class InvalidTaskOwnerAssignmentException extends RuntimeException
{
    public static function multiplePrimaryOwners(): self
    {
        return new self('Only one primary owner is allowed per task.');
    }
}
