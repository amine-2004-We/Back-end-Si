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
        Schema::create('budget_line_project', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_line_id')->constrained('budget_lines')->onDelete('cascade');
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->double('consumed_amount')->nullable();
            $table->integer('quantity')->nullable();
            $table->double('reliquate_amount')->nullable();
            $table->double('total_amount')->nullable();
            $table->double('remaining_amount')->nullable();
            $table->double('unit_amount')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_line_project');
    }
};
