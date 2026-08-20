<?php

namespace App\Domains\Annotation\Actions\DeleteAnnotation;

class DeleteAnnotationHandler
{
    public function handle(DeleteAnnotationCommand $command): void
    {
        $command->annotation->delete();
    }
}
