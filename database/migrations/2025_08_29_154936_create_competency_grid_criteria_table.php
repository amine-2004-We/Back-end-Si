<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competency_grid_criteria', function (Blueprint $table) {
            $table->id();

            $table->foreignId('competency_grid_id')
                ->constrained('competency_grids')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('criterion_id')
                ->constrained('competency_criteria')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->timestamps();

            $table->unique(['competency_grid_id', 'criterion_id']);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competency_grid_criteria');
    }
};
