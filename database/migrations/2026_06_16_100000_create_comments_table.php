<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();

            $table->ulid('commentable_id');
            $table->string('commentable_type');

            $table->foreignUlid('author_id')->constrained('users')->cascadeOnDelete();

            $table->text('content');

            $table->index('commentable_id');
            $table->index('commentable_type');
            $table->index(['commentable_type', 'commentable_id']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
