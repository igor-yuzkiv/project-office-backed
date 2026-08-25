<?php

namespace App\Http\Shared\Resources\ProjectDocuments;

use App\Domains\ProjectDocument\Models\ProjectDocumentVersionModel;
use App\Http\Shared\Resources\Users\UserOverviewResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ProjectDocumentVersionModel */
class ProjectDocumentVersionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'version_number' => $this->version_number,
            'label'          => $this->label,
            'content'        => $this->content,
            'is_primary'     => $this->isPrimary,

            'author' => $this->whenLoaded('author', fn () => new UserOverviewResource($this->author)),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
