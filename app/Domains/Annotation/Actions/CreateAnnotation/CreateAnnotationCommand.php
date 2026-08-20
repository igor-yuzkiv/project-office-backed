<?php

namespace App\Domains\Annotation\Actions\CreateAnnotation;

use App\Domains\User\Models\UserModel;
use App\Infrastructure\Models\Contracts\Annotatable;

class CreateAnnotationCommand
{
    /**
     * @param  array<string, mixed>  $anchor
     */
    public function __construct(
        public readonly Annotatable $annotatable,
        public readonly UserModel $author,
        public readonly string $content,
        public readonly array $anchor,
        public readonly ?string $textSnapshot = null,
    ) {}
}
