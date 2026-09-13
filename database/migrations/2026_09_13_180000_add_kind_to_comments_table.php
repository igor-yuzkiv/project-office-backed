<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->string('kind', 32)->default('comment')->after('content');
        });

        // Workflow comments written before the column existed are recognisable by their first line.
        DB::table('comments')->where('content', 'like', '# Checkpoint: %')->update(['kind' => 'checkpoint']);
        DB::table('comments')->where('content', 'like', '# Handoff%')->update(['kind' => 'handoff']);
        DB::table('comments')->where('content', 'like', '# Start%')->update(['kind' => 'start']);
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropColumn('kind');
        });
    }
};
