<?php

namespace App\Domains\Tag\Actions\CreateTag;

use App\Domains\Tag\Models\TagModel;

class CreateTagHandler
{
    public function handle(CreateTagCommand $command): TagModel
    {
        $normalizedName = strtolower(trim($command->name));

        return TagModel::create([
            'name'  => $normalizedName,
            'color' => $command->color,
        ]);
    }
}
