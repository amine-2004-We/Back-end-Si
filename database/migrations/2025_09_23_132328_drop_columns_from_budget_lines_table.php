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
        Schema::table('budget_lines', function (Blueprint $table) {
            //
            $table->dropColumn('remaining_amount');
            $table->dropColumn('commited_amount');
            $table->dropColumn('total_amount');
            $table->dropColumn('project_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_lines', function (Blueprint $table) {
            $table->integer('remaining_amount');
            $table->integer('commited_amount');
            $table->integer('total_amount');
            $table->foreignId('project_id')->constrained();
        });
    }
};
