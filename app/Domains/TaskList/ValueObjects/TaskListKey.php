<?php

namespace App\Domains\TaskList\ValueObjects;

readonly class TaskListKey
{
    public function __construct(
        public int $sequenceNumber,
        public string $value,
    ) {}
}
