<?php

namespace App\Http\Shared\Resources\Tags;

use App\Domains\Tag\Models\TagModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin TagModel */
class TagResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'color' => $this->color,
        ];
    }
}
