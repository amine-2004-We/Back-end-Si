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
        Schema::create('competency_criteria', function (Blueprint $table) {
            $table->id();
            $table->string('identifier')->unique();
            $table->string('title');
            $table->text('description');
            $table->enum('type', ['savoir', 'savoir-faire', 'savoir-etre'])->comment('Savoir, Savoir-faire, Savoir-être');
            $table->string('scoring_scale');
            $table->decimal('weight', 5, 2)->nullable()->comment('Pondération');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by_id')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competency_criteria');
    }
};
