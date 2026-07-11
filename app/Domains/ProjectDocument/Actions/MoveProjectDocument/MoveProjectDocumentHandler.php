<?php

namespace App\Domains\ProjectDocument\Actions\MoveProjectDocument;

use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use Illuminate\Support\Facades\DB;

class MoveProjectDocumentHandler
{
    public function handle(MoveProjectDocumentCommand $command): ProjectDocumentModel
    {
        $document = $command->document;

        if ($command->parentId === $document->parent_id) {
            return $document;
        }

        $newParentPath = $command->parentId !== null
            ? (string) ProjectDocumentModel::query()->where('id', $command->parentId)->value('path')
            : '';

        DB::transaction(function () use ($document, $command, $newParentPath): void {
            DB::statement(
                <<<'SQL'
                    UPDATE project_documents
                    SET path      = ?::ltree || subpath(path, nlevel(?::ltree) - 1),
                        depth     = nlevel(?::ltree || subpath(path, nlevel(?::ltree) - 1)) - 1,
                        parent_id = CASE WHEN id = ? THEN ? ELSE parent_id END
                    WHERE path <@ ?::ltree
                    SQL,
                [
                    $newParentPath, $document->path,
                    $newParentPath, $document->path,
                    $document->id, $command->parentId,
                    $document->path,
                ]
            );
        });

        return $document->fresh();
    }
}
