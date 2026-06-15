<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignUlid('task_list_id')->nullable()->constrained('task_lists')->nullOnDelete();

            $table->string('key')->unique();
            $table->unsignedInteger('sequence_number');
            $table->unique(['project_id', 'sequence_number']);

            $table->string('name');
            $table->longText('description')->nullable();

            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();

            $table->unsignedInteger('priority')->default(0);
            $table->string('status');

            $table->foreignUlid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUlid('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
