<?php

namespace App\Http\Shared\Resources\ProjectDocuments;

use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Http\Shared\Resources\Projects\ProjectOverviewResource;
use App\Http\Shared\Resources\Tags\TagResource;
use App\Http\Shared\Resources\Tasks\TaskOverviewResource;
use App\Http\Shared\Resources\Users\UserOverviewResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ProjectDocumentModel */
class ProjectDocumentOverviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'project_id' => $this->project_id,
            'parent_id'  => $this->parent_id,
            'key'        => $this->key,
            'title'      => $this->title,
            'status'     => $this->status->value,
            'depth'      => $this->depth,

            'project'    => $this->whenLoaded('project', fn () => new ProjectOverviewResource($this->project)),
            'tags'       => $this->whenLoaded('tags', fn () => TagResource::collection($this->tags)),
            'tasks'      => $this->whenLoaded('tasks', fn () => TaskOverviewResource::collection($this->tasks)),
            'created_by' => $this->whenLoaded('createdBy', fn () => new UserOverviewResource($this->createdBy)),
            'updated_by' => $this->whenLoaded('updatedBy', fn () => new UserOverviewResource($this->updatedBy)),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
