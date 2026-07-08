<?php

namespace App\Http\CliApi\Requests\Tasks\Concerns;

use App\Domains\Tag\DTO\CreateTagDTO;
use Illuminate\Support\Collection;
use Illuminate\Validation\Validator;

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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $tags = $this->input('tags');

            if (!is_string($tags)) {
                return;
            }

            $tooLong = $this->parseTagNames($tags)->first(fn (string $name): bool => mb_strlen($name) > 64);

            if ($tooLong !== null) {
                $validator->errors()->add('tags', "Tag name \"{$tooLong}\" exceeds the maximum length of 64 characters.");
            }
        });
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
            ->unique(fn (string $name): string => mb_strtolower($name))
            ->values();
    }
}
