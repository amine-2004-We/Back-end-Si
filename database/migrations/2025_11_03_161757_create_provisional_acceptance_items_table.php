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
        Schema::create('provisional_acceptance_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provisional_acceptance_id')->constrained('provisional_acceptances')->onDelete('cascade');

            $table->foreignId('article_id')->constrained('articles');

            $table->unsignedBigInteger('quantity_received');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provisional_acceptance_items');
    }
};
