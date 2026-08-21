<?php

namespace App\Domains\ProjectDocument\Exceptions;

use RuntimeException;

class ProjectDocumentParentProjectMismatchException extends RuntimeException
{
    public static function make(): self
    {
        return new self('A child document must belong to the same project as its parent.');
    }
}
