<?php

namespace App\Domains\Annotation\Actions\UpdateAnnotation;

use App\Domains\Annotation\Models\AnnotationModel;

class UpdateAnnotationCommand
{
    /**
     * @param  array<string, mixed>  $anchor
     */
    public function __construct(
        public readonly AnnotationModel $annotation,
        public readonly string $content,
        public readonly array $anchor,
        public readonly ?string $textSnapshot = null,
    ) {}
}
