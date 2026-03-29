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
        Schema::create('evaluations_hr', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->string('objectif_nature');
            $table->string('measurement_indicators');
            $table->integer('weight');
            $table->string('skill');
            $table->string('indicators');
            $table->string('value');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_evaluation_hr');
    }
};
