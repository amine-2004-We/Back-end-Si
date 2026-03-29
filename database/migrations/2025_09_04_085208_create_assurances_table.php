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
        Schema::create('insurances_training', function (Blueprint $table) {
            $table->id();
            $table->string('insurance_id')->unique();
            $table->morphs('personne_assuree');
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
        Schema::dropIfExists('insurances_training');
    }
};
