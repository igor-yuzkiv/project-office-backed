<?php

namespace App\Http\Resources\Tasks;

use App\Domains\Task\Models\TaskOwnerModel;
use App\Http\Resources\Users\UserOverviewResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin TaskOwnerModel */
class TaskOwnerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'user'       => $this->whenLoaded('user', fn () => new UserOverviewResource($this->user)),
            'role'       => $this->role?->value,
            'is_primary' => $this->is_primary,
        ];
    }
}
