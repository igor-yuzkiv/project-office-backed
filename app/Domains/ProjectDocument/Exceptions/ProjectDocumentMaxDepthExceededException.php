<?php

namespace App\Domains\ProjectDocument\Exceptions;

use RuntimeException;

class ProjectDocumentMaxDepthExceededException extends RuntimeException
{
    /** The wording lives on the model, which is what the limit belongs to. */
    public static function exceeded(string $message): self
    {
        return new self($message);
    }
}
