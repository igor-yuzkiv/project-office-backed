<?php

namespace App\Http\Shared\Resources\Users;

use App\Domains\User\Models\UserModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

/** @mixin UserModel */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'initials'   => $this->initials(),
            'avatar_url' => $this->current_avatar_attachment_id
                ? URL::route('attachments.content', ['attachment' => $this->current_avatar_attachment_id])
                : null,
        ];
    }
}
