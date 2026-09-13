<?php

namespace App\Http\Shared\Resources\Projects;

use App\Http\Shared\Resources\Users\UserOverviewResource;
use App\Libs\AuditTrail\Models\AuditRecordModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The newest audit event of a project, as the project card shows it.
 *
 * @mixin AuditRecordModel
 */
class ProjectLastActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'title'      => $this->title,
            'actor'      => $this->createdBy === null ? null : new UserOverviewResource($this->createdBy),
            'created_at' => $this->created_at,
        ];
    }
}
