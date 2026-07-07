<?php

namespace App\Http\Shared\Resources\Tasks;

use App\Domains\Task\Models\TaskModel;
use App\Domains\Task\ValueObjects\TaskPriorityData;
use App\Http\Shared\Resources\Projects\ProjectOverviewResource;
use App\Http\Shared\Resources\Tags\TagResource;
use App\Http\Shared\Resources\TaskLists\TaskListResource;
use App\Http\Shared\Resources\Users\UserOverviewResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin TaskModel */
class TaskOverviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'project_id'   => $this->project_id,
            'task_list_id' => $this->task_list_id,
            'key'          => $this->key,
            'name'         => $this->name,
            'start_date'   => $this->start_date?->toDateString(),
            'due_date'     => $this->due_date?->toDateString(),
            'priority'     => TaskPriorityData::from($this->priority)->toArray(),
            'status'       => $this->status->value,
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,

            'created_by' => $this->whenLoaded('createdBy', fn () => new UserOverviewResource($this->createdBy)),
            'updated_by' => $this->whenLoaded('updatedBy', fn () => new UserOverviewResource($this->updatedBy)),
            'tags'       => $this->whenLoaded('tags', fn () => TagResource::collection($this->tags)),
            'project'    => $this->whenLoaded('project', fn () => new ProjectOverviewResource($this->project)),
            'task_list'  => $this->whenLoaded('taskList', fn () => new TaskListResource($this->taskList)),
        ];
    }
}
