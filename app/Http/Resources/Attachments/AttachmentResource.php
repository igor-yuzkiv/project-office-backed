<?php

namespace App\Http\Resources\Attachments;

use App\Domains\Attachment\Models\AttachmentModel;
use App\Http\Resources\Users\UserOverviewResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AttachmentModel */
class AttachmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'original_name'    => $this->original_name,
            'extension'        => $this->extension,
            'mime_type'        => $this->mime_type,
            'size_bytes'       => $this->size_bytes,
            'storage_provider' => $this->storage_provider,
            'storage_key'      => $this->storage_key,
            'entity_type'      => $this->entity_type,
            'entity_id'        => $this->entity_id,
            'role'             => $this->role,
            'created_by'       => $this->whenLoaded('createdBy', fn () => new UserOverviewResource($this->createdBy)),
            'updated_by'       => $this->whenLoaded('updatedBy', fn () => new UserOverviewResource($this->updatedBy)),
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
        ];
    }
}
