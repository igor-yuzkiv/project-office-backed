<?php

namespace App\Http\Shared\Resources\TaskLists;

use App\Domains\TaskList\Models\TaskListModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Compact form used wherever a task list is nested inside another resource, so extending
 * TaskListResource does not silently grow the payload of tasks and projects.
 *
 * @mixin TaskListModel
 */
class TaskListOverviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'project_id' => $this->project_id,
            'key'        => $this->key,
            'name'       => $this->name,
            'status'     => $this->status->value,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
