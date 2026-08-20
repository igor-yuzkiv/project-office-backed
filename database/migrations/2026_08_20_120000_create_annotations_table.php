<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('annotations', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->ulid('annotatable_id');
            $table->string('annotatable_type');

            $table->foreignUlid('author_id')->constrained('users')->cascadeOnDelete();

            $table->text('content');
            $table->jsonb('anchor');
            $table->text('text_snapshot')->nullable();

            $table->index('annotatable_id');
            $table->index('annotatable_type');
            $table->index(['annotatable_type', 'annotatable_id']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('annotations');
    }
};
