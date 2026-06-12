<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taggables', function (Blueprint $table) {
            $table->foreignUlid('tag_id')->constrained('tags')->cascadeOnDelete();
            $table->ulid('taggable_id');
            $table->string('taggable_type');
            $table->timestamp('created_at')->nullable();

            $table->index(['taggable_id', 'taggable_type']);
            $table->index('tag_id');
            $table->unique(['tag_id', 'taggable_id', 'taggable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taggables');
    }
};
