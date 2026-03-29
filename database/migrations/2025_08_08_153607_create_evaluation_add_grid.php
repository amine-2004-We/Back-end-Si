
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
        Schema::create('evaluation_criteria', function (Blueprint $table) {
            $table->id();
            $table->string('criteria_code');
            $table->string('name');
            $table->foreignId('evaluation_grid_id')->constrained('evaluation_grid')->restrictOnDelete();
            $table->foreignId('object_project')->nullable()->constrained('projects')->restrictOnDelete();
            $table->foreignId('object_partner')->nullable()->constrained('partners')->restrictOnDelete();
            $table->string('description');
            $table->integer('grading_scale');
            $table->integer('weighting_criterion')->nullable();
            $table->integer('order');
            $table->string('comments')->nullable();
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
        Schema::dropIfExists('evaluation_criteria');
    }
};


