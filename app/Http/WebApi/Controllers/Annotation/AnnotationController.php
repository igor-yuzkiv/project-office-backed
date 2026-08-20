<?php

namespace App\Http\WebApi\Controllers\Annotation;

use App\Domains\Annotation\Actions\DeleteAnnotation\DeleteAnnotationCommand;
use App\Domains\Annotation\Actions\DeleteAnnotation\DeleteAnnotationHandler;
use App\Domains\Annotation\Actions\UpdateAnnotation\UpdateAnnotationCommand;
use App\Domains\Annotation\Actions\UpdateAnnotation\UpdateAnnotationHandler;
use App\Domains\Annotation\Models\AnnotationModel;
use App\Http\Shared\Resources\Annotations\AnnotationResource;
use App\Http\WebApi\Requests\Annotation\UpdateAnnotationRequest;
use Illuminate\Http\JsonResponse;

class AnnotationController
{
    public function __construct(
        private readonly UpdateAnnotationHandler $updateHandler,
        private readonly DeleteAnnotationHandler $deleteHandler,
    ) {}

    public function update(UpdateAnnotationRequest $request, AnnotationModel $annotation): AnnotationResource
    {
        $annotation = $this->updateHandler->handle(new UpdateAnnotationCommand(
            annotation: $annotation,
            content: $request->validated('content'),
            anchor: $request->validated('anchor'),
            textSnapshot: $request->validated('text_snapshot'),
        ));

        $annotation->load('author');

        return new AnnotationResource($annotation);
    }

    public function destroy(AnnotationModel $annotation): JsonResponse
    {
        $this->deleteHandler->handle(new DeleteAnnotationCommand($annotation));

        return response()->json(['message' => 'Annotation deleted.']);
    }
}
