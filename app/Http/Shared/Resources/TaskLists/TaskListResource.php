<?php

namespace App\Http\Shared\Resources\TaskLists;

use App\Domains\TaskList\Models\TaskListModel;
use App\Http\Shared\Resources\Projects\ProjectOverviewResource;
use App\Http\Shared\Resources\Tags\TagResource;
use App\Http\Shared\Resources\Tasks\TaskResource;
use App\Http\Shared\Resources\Users\UserOverviewResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin TaskListModel */
class TaskListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'project_id'      => $this->project_id,
            'key'             => $this->key,
            'sequence_number' => $this->sequence_number,
            'name'            => $this->name,
            'status'          => $this->status->value,
            'description'     => $this->description,
            'tasks_count'     => $this->whenCounted('tasks', fn () => $this->tasks_count),

            'tags'       => $this->whenLoaded('tags', fn () => TagResource::collection($this->tags)),
            'tasks'      => $this->whenLoaded('tasks', fn () => TaskResource::collection($this->tasks)),
            'project'    => $this->whenLoaded('project', fn () => new ProjectOverviewResource($this->project)),
            'created_by' => $this->whenLoaded('createdBy', fn () => new UserOverviewResource($this->createdBy)),
            'updated_by' => $this->whenLoaded('updatedBy', fn () => new UserOverviewResource($this->updatedBy)),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
