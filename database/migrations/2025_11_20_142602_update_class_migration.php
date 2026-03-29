<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('TRUNCATE TABLE class CASCADE;');
        Schema::table('class', function (Blueprint $table) {
            $table->foreignId('project_id')->constrained('projects')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class', function (Blueprint $table) {
            $table->dropForeign('project_id');
            $table->dropColumn('project_id');
        });
    }
};
