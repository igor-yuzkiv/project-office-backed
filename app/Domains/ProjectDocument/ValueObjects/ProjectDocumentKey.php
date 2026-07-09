<?php

namespace App\Domains\ProjectDocument\ValueObjects;

readonly class ProjectDocumentKey
{
    public function __construct(
        public int $sequenceNumber,
        public string $value,
    ) {}
}
