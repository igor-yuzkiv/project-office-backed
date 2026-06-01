<?php

namespace App\Http\Resources\Tasks;

use App\Domains\Task\Models\TaskModel;
use App\Http\Resources\Projects\ProjectResource;
use App\Http\Resources\TaskLists\TaskListResource;
use App\Http\Resources\Users\UserOverviewResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin TaskModel */
class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'project_id'      => $this->project_id,
            'task_list_id'    => $this->task_list_id,
            'key'             => $this->key,
            'sequence_number' => $this->sequence_number,
            'name'            => $this->name,
            'description'     => $this->description,
            'priority'        => ['value' => $this->priority->value, 'name' => $this->priority->name],
            'status'          => $this->status->value,
            'created_by'      => $this->whenLoaded('createdBy', fn () => new UserOverviewResource($this->createdBy)),
            'updated_by'      => $this->whenLoaded('updatedBy', fn () => new UserOverviewResource($this->updatedBy)),
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,

            'project'         => $this->whenLoaded('project', fn () => new ProjectResource($this->project)),
            'task_list'       => $this->whenLoaded('taskList', fn () => new TaskListResource($this->taskList)),
        ];
    }
}
