<?php

use Database\Backfills\ProjectDocumentContentBackfill;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_document_versions', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('project_document_id')->constrained('project_documents')->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('label')->nullable();
            $table->longText('content')->nullable();
            $table->foreignUlid('author_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->unique(['project_document_id', 'version_number']);
        });

        Schema::table('project_documents', function (Blueprint $table) {
            $table->foreignUlid('primary_version_id')->nullable()->after('title')
                ->constrained('project_document_versions')->nullOnDelete();
        });

        (new ProjectDocumentContentBackfill)->run();

        Schema::table('project_documents', function (Blueprint $table) {
            $table->dropColumn('content');
        });
    }

    public function down(): void
    {
        Schema::table('project_documents', function (Blueprint $table) {
            $table->longText('content')->nullable()->after('title');
        });

        // Restoring the column is not enough: without the content the effective version holds,
        // rolling back would silently empty every document.
        DB::statement(<<<'SQL'
            UPDATE project_documents AS d
            SET content = v.content
            FROM project_document_versions AS v
            WHERE v.id = COALESCE(
                d.primary_version_id,
                (
                    SELECT latest.id
                    FROM project_document_versions AS latest
                    WHERE latest.project_document_id = d.id
                    ORDER BY latest.version_number DESC
                    LIMIT 1
                )
            )
        SQL);

        Schema::table('project_documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('primary_version_id');
        });

        Schema::dropIfExists('project_document_versions');
    }
};
