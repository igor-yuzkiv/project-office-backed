<?php

namespace App\Domains\ProjectDocument\Actions\UpdateProjectDocument;

use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use DomainException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateProjectDocumentHandler
{
    public function handle(UpdateProjectDocumentCommand $command): ProjectDocumentModel
    {
        return DB::transaction(function () use ($command): ProjectDocumentModel {
            $document = $command->document;

            $isReparenting = array_key_exists('parent_id', $command->attributes)
                && $command->attributes['parent_id'] !== $document->parent_id;

            $oldPath = $document->path;
            // Height of the moved subtree (deepest descendant relative to the node), captured
            // while the subtree still sits at its old path. Measured before the move so it can be
            // combined with the node's new depth once the model has recomputed it.
            $subtreeHeight = $isReparenting ? $this->subtreeHeight($oldPath) : 0;

            try {
                if ($command->attributes !== []) {
                    // Runs the model's updating hook (applyHierarchy): recomputes this node's
                    // path/depth and enforces the self-parent, cross-project, descendant-cycle
                    // and parent-at-max-depth guards.
                    $document->update($command->attributes);
                }

                if ($isReparenting) {
                    if ($document->depth + $subtreeHeight > ProjectDocumentModel::MAX_DEPTH) {
                        throw ValidationException::withMessages([
                            'parent_id' => ['Maximum document nesting depth ('.(ProjectDocumentModel::MAX_DEPTH + 1).' levels) exceeded.'],
                        ]);
                    }

                    $this->cascadeDescendants($document, $oldPath);
                }
            } catch (DomainException $exception) {
                throw ValidationException::withMessages(['parent_id' => [$exception->getMessage()]]);
            } catch (UniqueConstraintViolationException) {
                throw ValidationException::withMessages([
                    'parent_id' => ['A document with this title already exists under the selected parent.'],
                ]);
            }

            if ($command->tagIds !== null) {
                $document->tags()->sync($command->tagIds);
            }

            return $document->fresh();
        });
    }

    private function subtreeHeight(string $oldPath): int
    {
        $maxLevel = (int) ProjectDocumentModel::query()
            ->whereRaw('path <@ ?::ltree', [$oldPath])
            ->selectRaw('MAX(nlevel(path)) as max_level')
            ->value('max_level');

        return $maxLevel - $this->nlevel($oldPath);
    }

    /**
     * Rewrite every descendant's path/depth after the moved node received its new path.
     * The node itself is already updated by the model's applyHierarchy() and is excluded here.
     */
    private function cascadeDescendants(ProjectDocumentModel $document, string $oldPath): void
    {
        $newNodePath = $document->path;

        DB::update(
            'UPDATE project_documents
             SET path = ?::ltree || subpath(path, nlevel(?::ltree)),
                 depth = nlevel(?::ltree || subpath(path, nlevel(?::ltree))) - 1
             WHERE path <@ ?::ltree AND id != ?',
            [$newNodePath, $oldPath, $newNodePath, $oldPath, $oldPath, $document->id],
        );
    }

    private function nlevel(string $path): int
    {
        return substr_count($path, '.') + 1;
    }
}
