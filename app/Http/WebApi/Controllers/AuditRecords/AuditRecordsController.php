<?php

namespace App\Http\WebApi\Controllers\AuditRecords;

use App\Http\Shared\Resources\AuditTrail\AuditRecordResource;
use App\Http\WebApi\Controllers\ResourceController;
use App\Http\WebApi\Requests\Shared\SearchRequest;
use App\Libs\AuditTrail\Models\AuditRecordModel;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AuditRecordsController extends ResourceController
{
    protected function getAllowedIncludes(): array
    {
        return [];
    }

    public function index(SearchRequest $request): AnonymousResourceCollection
    {
        $pagination = $this->getPaginationParams();

        $records = AuditRecordModel::with(['createdBy', 'project', 'subject'])
            ->filter((array) $request->input('filters', []))
            // The feed is chronological by definition, and the id is a ULID, so id desc is
            // newest first. sort_by / sort_order are deliberately not read here.
            ->orderByDesc('id')
            ->paginate($pagination->perPage, page: $pagination->page);

        return AuditRecordResource::collection($records);
    }
}
