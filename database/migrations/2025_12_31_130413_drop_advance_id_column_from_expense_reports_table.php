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
        Schema::table('expense_reports', function (Blueprint $table) {
            $table->dropColumn('advance_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expense_reports', function (Blueprint $table) {
            $table->unsignedBigInteger('advance_id')->nullable();
            $table->foreign('advance_id')->references('id')->on('advances');
        });
    }
};
