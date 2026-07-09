<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_document_task', function (Blueprint $table) {
            $table->foreignUlid('project_document_id')->constrained('project_documents')->cascadeOnDelete();
            $table->foreignUlid('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->timestamps();

            $table->index('task_id');
            $table->unique(['project_document_id', 'task_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_document_task');
    }
};
