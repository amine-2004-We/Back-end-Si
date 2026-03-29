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
        Schema::create('avenants', function (Blueprint $table) {
            $table->id();
            $table->string('avenant_id')->unique();
            $table->foreignId('marche_id')->constrained('calltenders')->onDelete('cascade');
            $table->text('subject');
            $table->enum('modification_nature', ['Financier', 'Périmètre', 'Durée', 'Autre']);
            $table->decimal('additional_amount', 15, 2)->nullable();
            $table->date('new_end_date')->nullable();
            $table->string('document_path');
            $table->foreignId('responsible_id')->constrained('collaborators');
            $table->enum('status', ['En préparation', 'Signé', 'Annulé'])->default('En préparation');
            $table->date('signature_date');
            $table->text('observations')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avenants');
    }
};
