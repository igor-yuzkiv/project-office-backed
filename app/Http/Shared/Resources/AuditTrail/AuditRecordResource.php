<?php

namespace App\Http\Shared\Resources\AuditTrail;

use App\Http\Shared\Resources\Users\UserOverviewResource;
use App\Libs\AuditTrail\Models\AuditRecordModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

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

            'subject' => $this->subject_type === null ? null : [
                'type' => $this->subjectTypeKey(),
                'id'   => $this->subject_id,
            ],
            // Always present, never conditional on the eager load: an absent key and a null
            // author are different things to whoever reads this.
            'actor' => $this->createdBy === null ? null : new UserOverviewResource($this->createdBy),
        ];
    }

    /**
     * The database stores the model class; the namespace has no business on the frontend, and a
     * short key is derived from the class name rather than kept in a map.
     */
    private function subjectTypeKey(): string
    {
        return Str::snake(Str::replaceLast('Model', '', class_basename((string) $this->subject_type)));
    }
}
