<?php

namespace Database\Backfills;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Moves the content of every project document into its first version. Documents whose content
 * is empty get no version at all — a document without versions is a valid state.
 */
class ProjectDocumentContentBackfill
{
    public function run(): void
    {
        $documents = DB::table('project_documents')
            ->whereNotNull('content')
            ->where('content', '<>', '')
            ->orderBy('id')
            ->get(['id', 'content', 'created_at', 'updated_at']);

        foreach ($documents as $document) {
            DB::table('project_document_versions')->insert([
                'id'                  => (string) Str::ulid(),
                'project_document_id' => $document->id,
                'version_number'      => 1,
                'label'               => null,
                'content'             => $document->content,
                'author_id'           => null,
                'created_at'          => $document->created_at,
                'updated_at'          => $document->updated_at,
            ]);
        }
    }
}
