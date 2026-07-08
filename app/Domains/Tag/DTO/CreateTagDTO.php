<?php

namespace App\Domains\Tag\DTO;

use App\Support\ColorUtil;

class CreateTagDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $color = null,
    ) {}

    public function getColor(): string
    {
        return $this->color ?? ColorUtil::randomHexColor();
    }

    public function getNormalizedName(): string
    {
        return strtolower(trim($this->name));
    }
}
