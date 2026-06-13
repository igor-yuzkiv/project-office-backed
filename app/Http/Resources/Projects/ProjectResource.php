<?php

namespace App\Http\Resources\Projects;

use App\Domains\Project\Models\ProjectModel;
use App\Http\Resources\Tags\TagResource;
use App\Http\Resources\TaskLists\TaskListResource;
use App\Http\Resources\Tasks\TaskResource;
use App\Http\Resources\Users\UserOverviewResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ProjectModel */
class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'prefix'     => $this->prefix,
            'status'     => $this->status->value,
            'created_by' => $this->whenLoaded('createdBy', fn () => new UserOverviewResource($this->createdBy)),
            'updated_by' => $this->whenLoaded('updatedBy', fn () => new UserOverviewResource($this->updatedBy)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'tags'       => $this->whenLoaded('tags', fn () => TagResource::collection($this->tags)),
            'tasks'      => $this->whenLoaded('tasks', fn () => TaskResource::collection($this->tasks)),
            'task_lists' => $this->whenLoaded('taskLists', fn () => TaskListResource::collection($this->taskLists)),
        ];
    }
}
