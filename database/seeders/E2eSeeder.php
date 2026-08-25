<?php

namespace Database\Seeders;

use App\Domains\Project\Models\ProjectModel;
use App\Domains\ProjectDocument\Enums\ProjectDocumentStatus;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Deterministic dataset for Playwright e2e runs.
 *
 * Holds a known user plus one project document whose markdown covers every block
 * type the annotation anchors distinguish. Extend here as new e2e scenarios need
 * fixture data — credentials are mirrored by the E2E_USER_* variables in .env.e2e.
 */
class E2eSeeder extends Seeder
{
    /** Markdown covering every block type the annotation anchors have to tell apart. */
    private const DOCUMENT_CONTENT = <<<'MD'
        # Annotated document

        The first paragraph of the annotated document.

        - Tight list item one
        - Tight list item two

        > A quoted line.

        ```php
        echo 'fenced code has no data-line';
        ```
        MD;

    public function run(): void
    {
        UserModel::updateOrCreate(
            ['email' => 'e2e@example.com'],
            [
                'name'              => 'E2E User',
                'email_verified_at' => now(),
                'password'          => Hash::make('password'),
            ],
        );

        $project = ProjectModel::updateOrCreate(
            ['prefix' => 'E2E'],
            ['name' => 'E2E Project', 'status' => 'active'],
        );

        $document = ProjectDocumentModel::updateOrCreate(
            ['key' => 'DOC-E2E-1'],
            [
                'project_id'      => $project->id,
                'sequence_number' => 1,
                'title'           => 'Annotated Document',
                'status'          => ProjectDocumentStatus::Draft->value,
            ],
        );

        $document->versions()->updateOrCreate(
            ['version_number' => 1],
            ['content' => self::DOCUMENT_CONTENT],
        );
    }
}
