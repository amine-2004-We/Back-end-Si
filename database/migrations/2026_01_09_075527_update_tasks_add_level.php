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
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('level_id')->nullable()->constrained('levels')->restrictOnDelete();
            $table->string('activities')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign('tasks_level_id_foreign');
            $table->dropColumn('level_id');
            $table->dropColumn('activities');
        });
    }
};
