<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('class', function (Blueprint $table) {
           if (!Schema::hasColumn('class', 'levels_id')) {
                $table->foreignId('levels_id')
                ->constrained('levels')
                ->restrictOnDelete();
           }
           if (!Schema::hasColumn('class', 'cycle_id')) {
                $table->foreignId('cycle_id')
                ->constrained('cycles')
                ->restrictOnDelete();
           }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class', function (Blueprint $table) {
            if (Schema::hasColumn('class', 'levels_id')) {
                $table->dropForeign(['levels_id']);
                $table->dropColumn('levels_id');
            }
            if (Schema::hasColumn('class', 'cycle_id')) {
                $table->dropForeign(['cycle_id']);
                $table->dropColumn('cycle_id');
            }
        });
    }
};
