<?php

namespace App\Domains\Tag\Actions\CreateTags;

use App\Domains\Tag\DTO\CreateTagDTO;
use Illuminate\Support\Collection;

class CreateTagsCommand
{
    /**
     * @param  Collection<int, CreateTagDTO>  $dtos
     */
    public function __construct(
        public readonly Collection $dtos,
    ) {}
}
