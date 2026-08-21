<?php

namespace App\Domains\Annotation\Actions\UpdateAnnotation;

use App\Domains\Annotation\Models\AnnotationModel;

class UpdateAnnotationHandler
{
    public function handle(UpdateAnnotationCommand $command): AnnotationModel
    {
        $command->annotation->update([
            'content'       => $command->content,
            'anchor'        => $command->anchor->toArray(),
            'text_snapshot' => $command->textSnapshot,
        ]);

        return $command->annotation;
    }
}
