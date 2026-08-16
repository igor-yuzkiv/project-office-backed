<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_records', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('type');
            // text, not string: titles carry task, list and file names, all varchar(255) themselves,
            // so a long one would overflow a string column and fail the business action that logged it.
            $table->text('title');
            $table->text('description')->nullable();
            $table->string('subject_type')->nullable();
            $table->string('subject_id')->nullable();
            $table->foreignUlid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->nullable();

            $table->index(['subject_type', 'subject_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_records');
    }
};
