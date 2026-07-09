<?php

namespace App\Http\Shared\Resources\ProjectDocuments;

use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Http\Shared\Resources\Projects\ProjectOverviewResource;
use App\Http\Shared\Resources\Tags\TagResource;
use App\Http\Shared\Resources\Tasks\TaskOverviewResource;
use App\Http\Shared\Resources\Users\UserOverviewResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/** @mixin ProjectDocumentModel */
class ProjectDocumentResource extends JsonResource
{
    /** @var Collection<int, ProjectDocumentModel>|null */
    private ?Collection $ancestorPath = null;

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
            'id'         => $this->id,
            'project_id' => $this->project_id,
            'parent_id'  => $this->parent_id,
            'key'        => $this->key,
            'title'      => $this->title,
            'content'    => $this->content,
            'status'     => $this->status->value,
            'depth'      => $this->depth,

            'project'    => $this->whenLoaded('project', fn () => new ProjectOverviewResource($this->project)),
            'tags'       => $this->whenLoaded('tags', fn () => TagResource::collection($this->tags)),
            'tasks'      => $this->whenLoaded('tasks', fn () => TaskOverviewResource::collection($this->tasks)),
            'path'       => $this->when($this->ancestorPath !== null, fn () => ProjectDocumentPathNodeResource::collection($this->ancestorPath)),
            'created_by' => $this->whenLoaded('createdBy', fn () => new UserOverviewResource($this->createdBy)),
            'updated_by' => $this->whenLoaded('updatedBy', fn () => new UserOverviewResource($this->updatedBy)),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
