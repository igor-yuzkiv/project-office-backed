<?php

namespace App\Http\CliApi\Resources\ProjectDocuments;

use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Http\Shared\Resources\ProjectDocuments\ProjectDocumentPathNodeResource;
use App\Http\Shared\Resources\Tags\TagResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/** @mixin ProjectDocumentModel */
class ProjectDocumentResource extends JsonResource
{
    /**
     * The document's ancestor chain (root first, the document itself last),
     * always present in the CLI response.
     *
     * @var Collection<int, ProjectDocumentModel>
     */
    private Collection $ancestorPath;

    /**
     * @param  Collection<int, ProjectDocumentModel>  $ancestorPath
     */
    public function withPath(Collection $ancestorPath): static
    {
        $this->ancestorPath = $ancestorPath;

        return $this;
    }

    public function toArray(Request $request): array
    {
        return [
            'id'      => $this->id,
            'key'     => $this->key,
            'title'   => $this->title,
            'status'  => $this->status->value,
            'content' => $this->content,
            'tags'    => TagResource::collection($this->whenLoaded('tags')),
            'path'    => ProjectDocumentPathNodeResource::collection($this->ancestorPath),
        ];
    }
}
