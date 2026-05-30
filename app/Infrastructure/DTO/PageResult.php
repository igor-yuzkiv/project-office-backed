<?php

namespace App\Infrastructure\DTO;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * @template T
 */
class PageResult implements Arrayable
{
    /**
     * @param  Collection<string, T>  $data
     */
    public function __construct(
        public Collection $data,
        public int $page,
        public int $perPage,
        public int $total,
        public int $lastPage,
        public bool $hasMore = false,
    ) {}

    public function toArray(): array
    {
        return [
            'data' => $this->data->toArray(),
            'meta' => $this->getMetadata(),
        ];
    }

    public function getMetadata(): array
    {
        return [
            'page'      => $this->page,
            'per_page'  => $this->perPage,
            'total'     => $this->total,
            'last_page' => $this->lastPage,
            'has_more'  => $this->hasMore,
        ];
    }

    public function isEmpty(): bool
    {
        return $this->data->isEmpty();
    }
}
