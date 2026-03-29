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
        Schema::table('position', function (Blueprint $table) {
            $table->string('position_code');
            $table->string('description')->nullable();
            $table->string('main_mission')->nullable();
            $table->string('key_activities')->nullable();
            $table->string('required_skills')->nullable();
            $table->string('link_with_function')->nullable();
            $table->string('version')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('position', function (Blueprint $table) {
            $table->dropColumn('description');
            $table->dropColumn('main_mission');
            $table->dropColumn('key_activities');
            $table->dropColumn('required_skills');
            $table->dropColumn('link_with_function');
            $table->dropColumn('version');
        });
    }
};
