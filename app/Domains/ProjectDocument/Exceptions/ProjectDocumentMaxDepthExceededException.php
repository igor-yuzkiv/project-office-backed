<?php

namespace App\Domains\ProjectDocument\Exceptions;

use RuntimeException;

class ProjectDocumentMaxDepthExceededException extends RuntimeException
{
    /** @param  int  $levels  How many levels of nesting are allowed, counting the root. */
    public static function withLevels(int $levels): self
    {
        return new self("Maximum document nesting depth ({$levels} levels) exceeded.");
    }
}
