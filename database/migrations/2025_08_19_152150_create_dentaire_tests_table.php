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
        Schema::create('dentaire_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beneficiaire_id')->constrained()->onDelete('cascade');
            $table->boolean('has_six_year_molar'); // dent de 6 ans
            $table->boolean('six_year_molar_cariee'); // cariée ?
            $table->boolean('refer_to_center'); // référer au centre ?
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
        Schema::dropIfExists('dentaire_tests');
    }
};
