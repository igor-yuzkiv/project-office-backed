<?php

namespace App\Http\Shared\Resources\Projects;

use App\Domains\Project\Models\ProjectModel;
use App\Http\Shared\Resources\Tags\TagResource;
use App\Http\Shared\Resources\TaskLists\TaskListResource;
use App\Http\Shared\Resources\Tasks\TaskResource;
use App\Http\Shared\Resources\Users\UserOverviewResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ProjectModel */
class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'prefix'      => $this->prefix,
            'status'      => $this->status->value,
            'description' => $this->description,
            'start_date'  => $this->start_date?->toDateString(),
            'end_date'    => $this->end_date?->toDateString(),
            'archived_at' => $this->archived_at,
            'archived_by' => $this->whenLoaded('archivedBy', fn () => new UserOverviewResource($this->archivedBy)),
            'created_by'  => $this->whenLoaded('createdBy', fn () => new UserOverviewResource($this->createdBy)),
            'updated_by'  => $this->whenLoaded('updatedBy', fn () => new UserOverviewResource($this->updatedBy)),
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,

            'tags'       => $this->whenLoaded('tags', fn () => TagResource::collection($this->tags)),
            'tasks'      => $this->whenLoaded('tasks', fn () => TaskResource::collection($this->tasks)),
            'task_lists' => $this->whenLoaded('taskLists', fn () => TaskListResource::collection($this->taskLists)),
        ];
    }
}
