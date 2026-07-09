<?php

namespace App\Http\Shared\Resources\ProjectDocuments;

use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Http\Shared\Resources\Tags\TagResource;
use App\Http\Shared\Resources\Users\UserOverviewResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ProjectDocumentModel */
class ProjectDocumentTreeNodeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'parent_id'    => $this->parent_id,
            'key'          => $this->key,
            'title'        => $this->title,
            'status'       => $this->status->value,
            'depth'        => $this->depth,
            'has_children' => $this->children_count > 0,

            'tags'       => $this->whenLoaded('tags', fn () => TagResource::collection($this->tags)),
            'updated_by' => $this->whenLoaded('updatedBy', fn () => new UserOverviewResource($this->updatedBy)),

            'updated_at' => $this->updated_at,
        ];
    }
}
