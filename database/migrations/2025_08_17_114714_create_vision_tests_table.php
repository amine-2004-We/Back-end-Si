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
        Schema::create('vision_tests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('beneficiary_id')->unique(); 
            $table->tinyInteger('right_eye')->nullable();   // Note de 1 à 10
            $table->tinyInteger('left_eye')->nullable();    // Note de 1 à 10
            $table->boolean('refer_to_center')->default(false); // Oui ou Non
            $table->text('observations')->nullable();
            $table->timestamps();
            $table->foreign('beneficiary_id')
                  ->references('id')
                  ->on('beneficiaires')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vision_tests');
    }
};
