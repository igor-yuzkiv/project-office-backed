<?php

namespace App\Console\Commands;

use App\Domains\Project\Models\ProjectModel;
use App\Domains\ProjectDocument\Actions\CreateProjectDocument\CreateProjectDocumentCommand;
use App\Domains\ProjectDocument\Actions\CreateProjectDocument\CreateProjectDocumentHandler;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Debug-only tool: bulk-imports a folder tree of markdown files as project documents,
 * mirroring the folder structure as the document hierarchy. Images are stripped —
 * text content only. Not exposed via the API; run manually from the CLI when seeding
 * or migrating documentation into a project.
 */
class ImportProjectDocumentsFromMarkdownCommand extends Command
{
    protected $signature = 'project-documents:import {project : Project ID (ULID)} {path : Absolute path to the root folder containing markdown files}';

    protected $description = 'Import a folder tree of markdown files as project documents (text only, no images)';

    private int $created = 0;

    private int $skipped = 0;

    public function handle(CreateProjectDocumentHandler $createHandler): int
    {
        $project = ProjectModel::find($this->argument('project'));

        if (!$project instanceof ProjectModel) {
            $this->error("Project not found: {$this->argument('project')}");

            return self::FAILURE;
        }

        $rootPath = rtrim((string) $this->argument('path'), '/');

        if (!File::isDirectory($rootPath)) {
            $this->error("Path is not a directory: {$rootPath}");

            return self::FAILURE;
        }

        $this->importDirectory($createHandler, $project, $rootPath, null, 0);

        $this->newLine();
        $this->info("Done. Created: {$this->created}, skipped: {$this->skipped}.");

        return self::SUCCESS;
    }

    private function importDirectory(
        CreateProjectDocumentHandler $createHandler,
        ProjectModel $project,
        string $dirPath,
        ?string $parentId,
        int $depth
    ): void {
        if ($depth > ProjectDocumentModel::MAX_DEPTH) {
            $this->warn("Skipping (nesting too deep): {$dirPath}");
            $this->skipped++;

            return;
        }

        $entries = collect(scandir($dirPath) ?: [])
            ->reject(fn (string $entry) => in_array($entry, ['.', '..'], true))
            ->sort()
            ->values();

        foreach ($entries as $entry) {
            $entryPath = $dirPath.'/'.$entry;

            if (File::isDirectory($entryPath)) {
                $document = $this->createDocument($createHandler, $project, $parentId, $entry, null);

                if ($document !== null) {
                    $this->importDirectory($createHandler, $project, $entryPath, $document->id, $depth + 1);
                }

                continue;
            }

            if (strtolower(pathinfo($entry, PATHINFO_EXTENSION)) !== 'md') {
                continue;
            }

            $title = pathinfo($entry, PATHINFO_FILENAME);
            $content = $this->stripImages(File::get($entryPath));

            $this->createDocument($createHandler, $project, $parentId, $title, $content);
        }
    }

    private function createDocument(
        CreateProjectDocumentHandler $createHandler,
        ProjectModel $project,
        ?string $parentId,
        string $title,
        ?string $content
    ): ?ProjectDocumentModel {
        try {
            // Runs in its own savepoint: a unique-constraint violation on one document
            // must not poison the surrounding transaction (Postgres aborts the whole
            // transaction on error) and block every import after it.
            $document = DB::transaction(fn () => $createHandler->handle(new CreateProjectDocumentCommand(
                project: $project,
                title: $title,
                parentId: $parentId,
                content: $content,
            )));

            $this->line("Created: {$document->key} — {$title}");
            $this->created++;

            return $document;
        } catch (QueryException $exception) {
            $this->warn("Skipped '{$title}' (likely a duplicate title under the same parent): {$exception->getMessage()}");
            $this->skipped++;

            return null;
        }
    }

    private function stripImages(string $markdown): string
    {
        $markdown = preg_replace('/!\[[^\]]*\]\([^)]*\)/', '', $markdown) ?? $markdown;
        $markdown = preg_replace('/<img\b[^>]*>/i', '', $markdown) ?? $markdown;

        return preg_replace('/\n{3,}/', "\n\n", $markdown) ?? $markdown;
    }
}
