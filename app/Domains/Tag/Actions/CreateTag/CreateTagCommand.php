<?php

namespace App\Domains\Tag\Actions\CreateTag;

class CreateTagCommand
{
    public function __construct(
        public readonly string $name,
        public readonly string $color,
    ) {}
}
