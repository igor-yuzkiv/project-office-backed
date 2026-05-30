<?php

namespace App\Infrastructure\DTO;

class PaginationParams
{
    public function __construct(
        public int $page = 1,
        public int $perPage = 10
    ) {}
}
