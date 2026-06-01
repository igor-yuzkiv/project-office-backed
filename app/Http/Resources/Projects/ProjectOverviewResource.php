<?php

namespace App\Http\Resources\Projects;

use App\Domains\Project\Models\ProjectModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ProjectModel */
class ProjectOverviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'     => $this->id,
            'name'   => $this->name,
            'prefix' => $this->prefix,
        ];
    }
}
