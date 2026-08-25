<?php

namespace App\Http\WebApi\Requests\Concerns;

use App\Domains\ProjectDocument\Models\ProjectDocumentModel;

/**
 * The version requests all scope their rules to the document in the route, and `route()` returns
 * a bound model or a raw string depending on the route's own signature — this keeps the narrowing
 * in one place.
 */
trait ResolvesRoutedProjectDocument
{
    private function document(): ProjectDocumentModel
    {
        /** @var ProjectDocumentModel $document */
        $document = $this->route('project_document');

        return $document;
    }
}
