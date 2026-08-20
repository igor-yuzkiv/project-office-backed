<?php

namespace App\Domains\Annotation\Actions\DeleteAnnotation;

use App\Domains\Annotation\Models\AnnotationModel;

class DeleteAnnotationCommand
{
    public function __construct(
        public readonly AnnotationModel $annotation,
    ) {}
}
