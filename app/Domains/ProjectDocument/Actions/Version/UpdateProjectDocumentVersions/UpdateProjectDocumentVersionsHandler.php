<?php

namespace App\Domains\ProjectDocument\Actions\Version\UpdateProjectDocumentVersions;

use App\Domains\ProjectDocument\AuditRecords\ProjectDocumentVersionUpdatedAuditRecord;
use App\Domains\ProjectDocument\Models\ProjectDocumentVersionModel;
use App\Libs\AuditTrail\Facades\AuditTrail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class UpdateProjectDocumentVersionsHandler
{
    /**
     * One editing session can carry edits to several versions, and half of them landing is worse
     * than none of them: the writer would have no way to tell which half.
     *
     * @return Collection<int, ProjectDocumentVersionModel>
     */
    public function handle(UpdateProjectDocumentVersionsCommand $command): Collection
    {
        return DB::transaction(function () use ($command): Collection {
            /** @var Collection<int, ProjectDocumentVersionModel> $saved */
            $saved = new Collection;

            foreach ($command->versions as $attributes) {
                $version = $command->document->versions()->findOrFail($attributes['id']);

                $version->update([
                    'content' => $attributes['content'],
                    'label'   => $attributes['label'],
                ]);

                // Each version is its own entity, so each one that actually moved gets its own
                // line. A version sent back unchanged produces nothing.
                $changed = ProjectDocumentVersionUpdatedAuditRecord::reportableColumns(array_keys($version->getChanges()));

                if ($changed !== []) {
                    AuditTrail::capture(new ProjectDocumentVersionUpdatedAuditRecord($command->document, $version, $changed));
                }

                $saved->push($version);
            }

            // The content lives on the version rows, so without this the document's own
            // `updated_at` and `updated_by` would go stale while its text keeps changing.
            $command->document->touch();

            return $saved;
        });
    }
}
