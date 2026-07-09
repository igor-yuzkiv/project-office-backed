<?php

namespace App\Infrastructure\ValueObjects;

readonly class EntityRef
{
    public function __construct(
        public string $id,
        public string $module
    ) {}
}
