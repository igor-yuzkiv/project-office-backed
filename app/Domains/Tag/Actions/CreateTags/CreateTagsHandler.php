<?php

namespace App\Domains\Tag\Actions\CreateTags;

use App\Domains\Tag\DTO\CreateTagDTO;
use App\Domains\Tag\Models\TagModel;
use Illuminate\Support\Collection;

class CreateTagsHandler
{
    /**
     * @param  Collection<int, CreateTagDTO>  $dtos
     * @return Collection<int, TagModel>
     */
    public function handle(Collection $dtos): Collection
    {
        $dtosByNormalizedName = $dtos->keyBy(fn (CreateTagDTO $dto): string => $dto->getNormalizedName());

        $existingTags = TagModel::query()
            ->whereIn('name', $dtosByNormalizedName->keys())
            ->get()
            ->keyBy('name');

        $newTags = $dtosByNormalizedName->except($existingTags->keys())
            ->map(fn (CreateTagDTO $dto): TagModel => TagModel::create([
                'name'  => $dto->getNormalizedName(),
                'color' => $dto->getColor(),
            ]))
            ->keyBy('name');

        /** @var Collection<int, TagModel> $tags */
        $tags = $dtosByNormalizedName->keys()
            ->map(fn (string $name): TagModel => $existingTags[$name] ?? $newTags[$name])
            ->values();

        return $tags;
    }
}
