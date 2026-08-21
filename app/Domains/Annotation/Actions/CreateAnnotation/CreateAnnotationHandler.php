<?php

namespace App\Domains\Annotation\Actions\CreateAnnotation;

use App\Domains\Annotation\Models\AnnotationModel;

class CreateAnnotationHandler
{
    public function handle(CreateAnnotationCommand $command): AnnotationModel
    {
        /** @var AnnotationModel $annotation */
        $annotation = $command->annotatable->annotations()->create([
            'author_id'     => $command->author->id,
            'content'       => $command->content,
            'anchor'        => $command->anchor->toArray(),
            'text_snapshot' => $command->textSnapshot,
        ]);

        return $annotation;
    }
}
