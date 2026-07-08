<?php

namespace App\Http\WebApi\Controllers\Tags;

use App\Domains\Tag\Actions\CreateTags\CreateTagsHandler;
use App\Domains\Tag\Models\TagModel;
use App\Http\Shared\Resources\Tags\TagResource;
use App\Http\WebApi\Controllers\ResourceController;
use App\Http\WebApi\Requests\Tag\CreateTagRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TagsController extends ResourceController
{
    public function __construct(
        private readonly CreateTagsHandler $createHandler,
    ) {}

    protected function getAllowedIncludes(): array
    {
        return [];
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $search = $request->query('search');

        $query = TagModel::query()->orderBy('name', 'asc');

        if (is_string($search) && $search !== '') {
            $normalized = '%'.strtolower(trim($search)).'%';
            $query->where('name', 'like', $normalized);
        }

        $tags = $query->limit(50)->get();

        return TagResource::collection($tags);
    }

    public function store(CreateTagRequest $request): JsonResponse
    {
        $tag = $this->createHandler->handle(collect([$request->toDto()]))->first();

        return (new TagResource($tag))
            ->response()
            ->setStatusCode(201);
    }
}
