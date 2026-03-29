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
        Schema::table('externals', function (Blueprint $table) {
            $table->foreignId('training_group_id')
                ->nullable()
                ->after('participant_id')
                ->constrained('training_groups')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('externals', function (Blueprint $table) {
            $table->dropForeign(['training_group_id']);
            $table->dropColumn('training_group_id');
        });
    }
};
