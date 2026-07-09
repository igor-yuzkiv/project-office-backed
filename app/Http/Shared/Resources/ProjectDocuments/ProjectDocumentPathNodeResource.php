<?php

namespace App\Http\Shared\Resources\ProjectDocuments;

use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ProjectDocumentModel */
class ProjectDocumentPathNodeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'key'   => $this->key,
            'title' => $this->title,
        ];
    }
}
