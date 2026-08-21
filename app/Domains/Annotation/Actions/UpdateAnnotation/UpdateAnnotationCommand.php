<?php

namespace App\Domains\Annotation\Actions\UpdateAnnotation;

use App\Domains\Annotation\DTO\BlockAnchorDTO;
use App\Domains\Annotation\Models\AnnotationModel;

class UpdateAnnotationCommand
{
    public function __construct(
        public readonly AnnotationModel $annotation,
        public readonly string $content,
        public readonly BlockAnchorDTO $anchor,
        public readonly ?string $textSnapshot = null,
    ) {}
}
