<?php

namespace App\Domains\ProjectDocument\Actions\Document\DeleteProjectDocument;

use App\Domains\Attachment\Actions\DeleteAttachment\DeleteAttachmentCommand;
use App\Domains\Attachment\Actions\DeleteAttachment\DeleteAttachmentHandler;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use Illuminate\Support\Facades\DB;

class DeleteProjectDocumentHandler
{
    public function __construct(
        private readonly DeleteAttachmentHandler $deleteAttachmentHandler,
    ) {}

    public function handle(DeleteProjectDocumentCommand $command): void
    {
        DB::transaction(function () use ($command): void {
            $subtree = ProjectDocumentModel::query()
                ->whereRaw('path <@ ?::ltree', [$command->document->path])
                ->with(['attachments', 'versions'])
                ->orderByDesc('depth')
                ->get();

            foreach ($subtree as $node) {

                // TODO: implement bulk delete attachments
                foreach ($node->attachments as $attachment) {
                    $this->deleteAttachmentHandler->handle(new DeleteAttachmentCommand($attachment));
                }

                $node->comments()->delete();

                // Versions go with the document through the foreign key, but their annotations
                // are a polymorphic link with nothing to cascade along.
                foreach ($node->versions as $version) {
                    $version->annotations()->delete();
                }
                $node->tags()->detach();
                $node->delete();
            }
        });
    }
}
