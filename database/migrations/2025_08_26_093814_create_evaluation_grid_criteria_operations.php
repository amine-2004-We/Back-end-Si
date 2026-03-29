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
        Schema::create('evaluation_grid_criteria_operations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('evaluation_id');
            $table->unsignedBigInteger('grid_id');
            $table->unsignedBigInteger('criteria_id');
            $table->double('score');
            $table->foreign('evaluation_id')->references('id')->on('evaluation_operations')->onDelete('restrict');
            $table->foreign('grid_id')->references('id')->on('evaluation_grid_operations')->onDelete('restrict');
            $table->foreign('criteria_id')->references('id')->on('evaluation_criteria_operations')->onDelete('restrict');
            $table->unique(['evaluation_id', 'grid_id','criteria_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_grid_criteria_operations');
    }
};
