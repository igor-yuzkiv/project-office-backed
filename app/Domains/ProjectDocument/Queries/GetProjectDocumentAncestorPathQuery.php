<?php

namespace App\Domains\ProjectDocument\Queries;

use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use Illuminate\Support\Collection;

class GetProjectDocumentAncestorPathQuery
{
    /**
     * Returns the document's ancestor chain (root first, the document itself last),
     * decoded from its materialized `path` (ltree) rather than walking `parent_id`.
     *
     * @return Collection<int, ProjectDocumentModel>
     */
    public function handle(ProjectDocumentModel $document): Collection
    {
        $orderedIds = explode('.', (string) $document->path);

        $documentsById = ProjectDocumentModel::query()
            ->whereIn('id', $orderedIds)
            ->get(['id', 'key', 'title'])
            ->keyBy('id');

        return collect($orderedIds)
            ->map(fn (string $id) => $documentsById->get($id))
            ->filter()
            ->values();
    }
}
