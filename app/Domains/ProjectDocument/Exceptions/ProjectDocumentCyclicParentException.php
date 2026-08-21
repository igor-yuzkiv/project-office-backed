<?php

namespace App\Domains\ProjectDocument\Exceptions;

use RuntimeException;

/**
 * A document was given a parent that sits inside its own subtree. Being its own
 * parent is the shortest form of that cycle; a descendant is the longer one.
 */
class ProjectDocumentCyclicParentException extends RuntimeException
{
    public static function itself(): self
    {
        return new self('A document cannot be its own parent.');
    }

    public static function ownDescendant(): self
    {
        return new self('A document cannot be moved under its own descendant.');
    }
}
