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
        Schema::create('places', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['Centre de formation externe', 'Gratuit', 'Payant', 'Salle de formation ZA']);
            $table->enum('status', ['Actif', 'Annulé', 'En pause', 'Clôturé'])->default('Actif');
            $table->integer('capacity')->nullable();
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->text('address')->nullable();
            $table->foreignId('province_id')->constrained('provinces')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('places');
    }
};
