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
        Schema::table('competency_criteria', function (Blueprint $table) {
            $table->unsignedInteger('display_order')->nullable()->after('scoring_scale');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('competency_criteria', function (Blueprint $table) {
            $table->dropColumn('display_order');
        });
    }
};
