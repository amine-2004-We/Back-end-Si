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
        Schema::table('plan_type', function (Blueprint $table) {
            $table->json('responsible_title')->nullable();
            $table->integer('duration')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plan_type', function (Blueprint $table) {
            $table->dropColumn('responsible_title');
            $table->integer('duration');
        });
    }
};
