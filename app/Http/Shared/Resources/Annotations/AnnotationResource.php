<?php

namespace App\Http\Shared\Resources\Annotations;

use App\Domains\Annotation\Models\AnnotationModel;
use App\Http\Shared\Resources\Users\UserOverviewResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AnnotationModel */
class AnnotationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => (string) $this->id,
            'content'       => $this->content,
            'text_snapshot' => $this->text_snapshot,
            'anchor'        => $this->anchor,
            'author'        => new UserOverviewResource($this->author),
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
        ];
    }
}
