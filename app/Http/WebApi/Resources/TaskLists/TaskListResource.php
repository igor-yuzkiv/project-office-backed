<?php

namespace App\Http\WebApi\Resources\TaskLists;

use App\Domains\TaskList\Models\TaskListModel;
use App\Http\WebApi\Resources\Projects\ProjectOverviewResource;
use App\Http\WebApi\Resources\Tasks\TaskResource;
use App\Http\WebApi\Resources\Users\UserOverviewResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin TaskListModel */
class TaskListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'project_id'  => $this->project_id,
            'name'        => $this->name,
            'tasks_count' => $this->whenCounted('tasks', fn () => $this->tasks_count),

            'tasks'      => $this->whenLoaded('tasks', fn () => TaskResource::collection($this->tasks)),
            'project'    => $this->whenLoaded('project', fn () => new ProjectOverviewResource($this->project)),
            'created_by' => $this->whenLoaded('createdBy', fn () => new UserOverviewResource($this->createdBy)),
            'updated_by' => $this->whenLoaded('updatedBy', fn () => new UserOverviewResource($this->updatedBy)),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
