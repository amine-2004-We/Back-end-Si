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
        Schema::create('orl_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beneficiaire_id')->constrained()->onDelete('cascade');
            $table->boolean('ear_pain_regularly'); 
            $table->boolean('hearing_problem');
            $table->boolean('refer_to_center');
            $table->text('observations')->nullable();
            $table->date('consultation_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orl_tests');
    }
};
