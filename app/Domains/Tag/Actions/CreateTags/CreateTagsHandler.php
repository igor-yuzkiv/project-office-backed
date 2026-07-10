<?php

namespace App\Domains\Tag\Actions\CreateTags;

use App\Domains\Tag\DTO\CreateTagDTO;
use App\Domains\Tag\Models\TagModel;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Collection;

class CreateTagsHandler
{
    /**
     * @return Collection<int, TagModel>
     */
    public function handle(CreateTagsCommand $command): Collection
    {
        $dtosByNormalizedName = $command->dtos->keyBy(fn (CreateTagDTO $dto): string => $dto->getNormalizedName());

        $existingTags = TagModel::query()
            ->whereIn('name', $dtosByNormalizedName->keys())
            ->get()
            ->keyBy('name');

        $newTags = $dtosByNormalizedName->except($existingTags->keys())
            ->map(fn (CreateTagDTO $dto): TagModel => $this->createOrFetch($dto))
            ->keyBy('name');

        /** @var Collection<int, TagModel> $tags */
        $tags = $dtosByNormalizedName->keys()
            ->map(fn (string $name): TagModel => $existingTags[$name] ?? $newTags[$name])
            ->values();

        return $tags;
    }

    /**
     * A concurrent request may create the same tag between our existence check and this
     * insert; on a unique-constraint violation, another request already won, so we just
     * fetch its row instead of failing.
     */
    private function createOrFetch(CreateTagDTO $dto): TagModel
    {
        try {
            return TagModel::create([
                'name'  => $dto->getNormalizedName(),
                'color' => $dto->getColor(),
            ]);
        } catch (UniqueConstraintViolationException) {
            return TagModel::where('name', $dto->getNormalizedName())->firstOrFail();
        }
    }
}
