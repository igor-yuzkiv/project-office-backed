<?php

namespace App\Domains\ProjectDocument\Actions\UpdateProjectDocumentVersions;

use App\Domains\ProjectDocument\AuditRecords\ProjectDocumentUpdatedAuditRecord;
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

                $saved->push($version);
            }

            // The content lives on the version rows, so the document itself stays clean without this.
            $command->document->touch();

            AuditTrail::capture(new ProjectDocumentUpdatedAuditRecord($command->document));

            return $saved;
        });
    }
}
