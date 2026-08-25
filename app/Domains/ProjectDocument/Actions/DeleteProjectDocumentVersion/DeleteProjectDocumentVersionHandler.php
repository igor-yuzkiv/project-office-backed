<?php

namespace App\Domains\ProjectDocument\Actions\DeleteProjectDocumentVersion;

class DeleteProjectDocumentVersionHandler
{
    /**
     * Deleting the last version is allowed: a document without versions is an ordinary state.
     * A pin pointing at the deleted version is cleared by the foreign key, not here.
     */
    public function handle(DeleteProjectDocumentVersionCommand $command): void
    {
        $command->version->delete();
    }
}
