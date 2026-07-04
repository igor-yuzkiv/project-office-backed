<?php

namespace App\Http\WebApi\Resources\Comment;

use App\Domains\Comment\Models\CommentModel;
use App\Http\WebApi\Resources\Users\UserOverviewResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CommentModel */
class CommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => (string) $this->id,
            'content'    => $this->content,
            'author'     => new UserOverviewResource($this->author),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'can'        => [
                'update' => $request->user()?->can('update', $this->resource),
                'delete' => $request->user()?->can('delete', $this->resource),
            ],
        ];
    }
}
