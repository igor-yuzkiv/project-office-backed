<?php

namespace App\Domains\Shared\ValueObjects;

readonly class EntityRef
{
    public function __construct(
        public string $id,
        public string $module
    ) {}
}
