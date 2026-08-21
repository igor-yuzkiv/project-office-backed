<?php

namespace App\Domains\Attachment\Exceptions;

use RuntimeException;

class AttachmentStorageFailedException extends RuntimeException
{
    public static function couldNotStoreFile(): self
    {
        return new self('Attachment file could not be stored.');
    }
}
