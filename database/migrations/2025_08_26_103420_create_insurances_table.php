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
        Schema::create('insurances', function (Blueprint $table) {
            $table->id();
             $table->string('insurance_id')->unique();
             $table->foreignId('collaborator_id')->constrained('collaborators');
            $table->enum('insurance_type', ['CNSS', 'AMO', 'Retraite complémentaire']);
            $table->string('insurance_organization');
             $table->date('affiliation_date');
             $table->date('termination_date')->nullable();
              $table->text('comments')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insurances');
    }
};
