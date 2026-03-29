<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('final_acceptance_provisional_acceptance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('final_acceptance_id')->constrained('final_acceptances')->onDelete('cascade');
            $table->foreignId('provisional_acceptance_id')->constrained('provisional_acceptances')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['final_acceptance_id', 'provisional_acceptance_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('final_acceptance_provisional_acceptance');
    }
};
