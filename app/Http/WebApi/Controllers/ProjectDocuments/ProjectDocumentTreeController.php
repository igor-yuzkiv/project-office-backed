<?php

namespace App\Http\WebApi\Controllers\ProjectDocuments;

use App\Domains\Project\Models\ProjectModel;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Http\Shared\Resources\ProjectDocuments\ProjectDocumentTreeNodeResource;
use App\Http\WebApi\Controllers\ResourceController;
use App\Http\WebApi\Requests\ProjectDocuments\ProjectDocumentTreeRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectDocumentTreeController extends ResourceController
{
    protected function getAllowedIncludes(): array
    {
        return [];
    }

    public function index(ProjectDocumentTreeRequest $request, ProjectModel $project): AnonymousResourceCollection
    {
        $pagination = $this->getPaginationParams();

        $documents = ProjectDocumentModel::where('project_id', $project->id)
            ->where('parent_id', $request->validated('parent_id'))
            ->filter((array) $request->validated('filters', []))
            ->withCount('children')
            ->with(['tags', 'updatedBy'])
            ->orderBy('title')
            ->paginate($pagination->perPage, page: $pagination->page);

        return ProjectDocumentTreeNodeResource::collection($documents);
    }
}
