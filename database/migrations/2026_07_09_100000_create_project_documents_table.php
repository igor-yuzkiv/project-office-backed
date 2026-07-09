<?php

use App\Domains\ProjectDocument\Enums\ProjectDocumentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS ltree');

        Schema::create('project_documents', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('project_id')->constrained('projects')->cascadeOnDelete();
            $table->ulid('parent_id')->nullable();

            $table->string('title');
            $table->longText('content')->nullable();
            $table->string('status', 100)->default(ProjectDocumentStatus::Draft->value);
            $table->unsignedInteger('depth')->default(0);

            $table->timestamp('archived_at')->nullable();
            $table->foreignUlid('archived_by')->nullable()->constrained('users')->nullOnDelete();

            $table->foreignUlid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUlid('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index('status');
            $table->index('archived_at');
            $table->unique(['project_id', 'parent_id', 'title']);
        });

        // The FK on parent_id is added after table creation (self-reference), because
        // Postgres adds the primary key constraint after all inline foreign keys when
        // both are declared in the same create table blueprint, and a self-referencing
        // FK cannot be created before the primary key it targets exists.
        Schema::table('project_documents', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('project_documents')->nullOnDelete();
            $table->index('project_id');
            $table->index('parent_id');
        });

        DB::statement('ALTER TABLE project_documents ADD COLUMN path ltree NOT NULL');
        DB::statement('CREATE INDEX project_documents_path_gist_idx ON project_documents USING GIST (path)');

        // Postgres unique constraints treat NULL as distinct, so the (project_id, parent_id, title)
        // constraint above does not dedupe root-level titles (parent_id IS NULL). A partial unique
        // index covers that level explicitly.
        DB::statement('CREATE UNIQUE INDEX project_documents_root_title_unique ON project_documents (project_id, title) WHERE parent_id IS NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('project_documents');
    }
};
