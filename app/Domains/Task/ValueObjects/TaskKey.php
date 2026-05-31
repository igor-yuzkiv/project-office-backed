<?php

namespace App\Domains\Task\ValueObjects;

readonly class TaskKey
{
    public function __construct(
        public int $sequenceNumber,
        public string $value,
    ) {}
}
