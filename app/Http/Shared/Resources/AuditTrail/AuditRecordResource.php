<?php

namespace App\Http\Shared\Resources\AuditTrail;

use App\Http\Shared\Resources\Users\UserOverviewResource;
use App\Libs\AuditTrail\Models\AuditRecordModel;
use App\Libs\AuditTrail\SubjectType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AuditRecordModel
 */
class AuditRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'type'        => $this->type,
            'title'       => $this->title,
            'description' => $this->description,
            'created_at'  => $this->created_at,

            // key and name come from the subject row, so a deleted subject keeps its type and id
            // and loses only what there is no longer a row for.
            'subject' => $this->subject_type === null ? null : [
                'type' => SubjectType::keyOf($this->subject_type),
                'id'   => $this->subject_id,
                'key'  => $this->subject === null ? null : SubjectType::keyAttributeOf($this->subject),
                'name' => $this->subject === null ? null : SubjectType::nameOf($this->subject),
            ],
            'project' => $this->project === null ? null : [
                'id'     => $this->project->id,
                'name'   => $this->project->name,
                'prefix' => $this->project->prefix,
            ],
            // Always present, never conditional on the eager load: an absent key and a null
            // author are different things to whoever reads this.
            'actor' => $this->createdBy === null ? null : new UserOverviewResource($this->createdBy),
        ];
    }
}
