<?php

namespace App\Http\WebApi\Controllers\Tags;

use App\Domains\Tag\Actions\CreateTag\CreateTagCommand;
use App\Domains\Tag\Actions\CreateTag\CreateTagHandler;
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
        private readonly CreateTagHandler $createHandler,
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
        $color = $request->validated('color') ?? sprintf('#%06X', mt_rand(0, 0xFFFFFF));

        $command = new CreateTagCommand(
            name: $request->validated('name'),
            color: $color,
        );

        $tag = $this->createHandler->handle($command);

        return (new TagResource($tag))
            ->response()
            ->setStatusCode(201);
    }
}
