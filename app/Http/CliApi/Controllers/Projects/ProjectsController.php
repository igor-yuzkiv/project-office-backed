<?php

namespace App\Http\CliApi\Controllers\Projects;

use App\Domains\Project\Models\ProjectModel;
use App\Http\Shared\Resources\Projects\ProjectResource;
use App\Http\WebApi\Controllers\ResourceController;

class ProjectsController extends ResourceController
{
    protected function getAllowedIncludes(): array
    {
        return ['createdBy', 'updatedBy', 'archivedBy', 'tags'];
    }

    public function show(ProjectModel $project): ProjectResource
    {
        $project->load($this->resolveIncludes(required: ['createdBy', 'updatedBy', 'archivedBy', 'tags'], requested: $this->parseRequestedIncludes()));

        return new ProjectResource($project);
    }
}
