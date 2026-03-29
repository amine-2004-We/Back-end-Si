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
        Schema::create('pediatre_tests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('beneficiary_id')->unique(); 
            $table->integer('height')->nullable();
            $table->float('weight')->nullable();
            $table->boolean('refer_to_center')->default(false);
            $table->text('observations')->nullable();
            $table->date('date_consultation')->nullable();
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
        Schema::dropIfExists('pediatre_tests');
    }
};
