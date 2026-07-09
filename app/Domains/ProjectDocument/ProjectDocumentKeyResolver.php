<?php

namespace App\Domains\ProjectDocument;

use App\Domains\Project\Models\ProjectModel;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Domains\ProjectDocument\ValueObjects\ProjectDocumentKey;

class ProjectDocumentKeyResolver
{
    // TODO: wrap in a database lock to prevent race conditions under concurrent requests
    public function resolve(ProjectModel $project): ProjectDocumentKey
    {
        $sequenceNumber = (int) ProjectDocumentModel::where('project_id', $project->id)->max('sequence_number') + 1;

        return new ProjectDocumentKey(
            sequenceNumber: $sequenceNumber,
            value: 'DOC-'.$project->prefix.'-'.$sequenceNumber,
        );
    }
}
