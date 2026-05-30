<?php

use App\Infrastructure\Models\UserModel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->string('name');

            $table->foreignIdFor(UserModel::class, 'created_by')->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(UserModel::class, 'updated_by')->nullable()->constrained()->nullOnDelete();

            $table->fullText('name');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
