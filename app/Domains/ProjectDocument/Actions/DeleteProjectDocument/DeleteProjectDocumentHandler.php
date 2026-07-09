<?php

namespace App\Domains\ProjectDocument\Actions\DeleteProjectDocument;

use App\Domains\Attachment\Actions\DeleteAttachment\DeleteAttachmentHandler;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use Illuminate\Support\Facades\DB;

class DeleteProjectDocumentHandler
{
    public function __construct(
        private readonly DeleteAttachmentHandler $deleteAttachmentHandler,
    ) {}

    public function handle(ProjectDocumentModel $document): void
    {
        DB::transaction(function () use ($document): void {
            $subtree = ProjectDocumentModel::query()
                ->whereRaw('path <@ ?::ltree', [$document->path])
                ->with('attachments')
                ->orderByDesc('depth')
                ->get();

            foreach ($subtree as $node) {
                foreach ($node->attachments as $attachment) {
                    $this->deleteAttachmentHandler->handle($attachment);
                }

                $node->comments()->delete();
                $node->tags()->detach();
                $node->delete();
            }
        });
    }
}
