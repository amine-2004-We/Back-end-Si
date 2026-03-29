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
        Schema::create('evaluation_grid', function (Blueprint $table) {
            $table->id();
            $table->string('grid_code');
            $table->string('title');
            $table->foreignId('object_project')->nullable()->constrained('projects')->restrictOnDelete();
            $table->foreignId('object_partner')->nullable()->constrained('partners')->restrictOnDelete();
            $table->string('evaluation_frequency')->nullable();
            $table->string('scoring_method');
            $table->string('rating_scale');
            $table->boolean('weighting_criterion');
            $table->string('grid_status');
            $table->string('comment')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_grid');
    }
};
