<?php

namespace App\Http\WebApi\Resources\Users;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Laravel\Sanctum\PersonalAccessToken;

/** @mixin PersonalAccessToken */
class ApiTokenResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => (string) $this->id,
            'name'       => $this->name,
            'expires_at' => $this->expires_at,
            'created_at' => $this->created_at,
        ];
    }
}
