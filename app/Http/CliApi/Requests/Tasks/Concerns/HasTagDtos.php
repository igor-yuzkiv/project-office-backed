<?php

namespace App\Http\CliApi\Requests\Tasks\Concerns;

use App\Domains\Tag\DTO\CreateTagDTO;
use Illuminate\Support\Collection;

trait HasTagDtos
{
    /**
     * @return Collection<int, CreateTagDTO>
     */
    public function getTagDtos(): Collection
    {
        return $this->parseTagNames($this->validated('tags'))
            ->map(fn (string $name): CreateTagDTO => new CreateTagDTO(name: $name));
    }

    /**
     * @return Collection<int, non-empty-string>
     */
    private function parseTagNames(?string $tags): Collection
    {
        if ($tags === null) {
            return collect();
        }

        return collect(explode(',', $tags))
            ->map(fn (string $name): string => trim($name))
            ->filter(fn (string $name): bool => $name !== '')
            ->unique()
            ->values();
    }
}
