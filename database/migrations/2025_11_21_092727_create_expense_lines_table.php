<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('expense_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_report_id')->constrained()->cascadeOnDelete();
            
            // Designation et type avec enum
            $table->string('designation');
            $table->enum('type', [
                'restauration', 
                'deplacement', 
                'hebergement',
                'transport',
                'autre'
            ]);
            
            // Champs conditionnels pour déplacement
            $table->foreignId('departure_id')->nullable()->constrained('provinces')->nullOnDelete();
            $table->foreignId('arrival_id')->nullable()->constrained('provinces')->nullOnDelete();
            $table->enum('transport_mode', ['train', 'taxi', 'avion', 'voiture', 'bus'])->nullable();
            
            // Informations générales
            $table->date('date');
            $table->string('label')->nullable();
            
            // Montants
            $table->decimal('amount', 10, 2);
            $table->decimal('amount_manager', 10, 2)->nullable();
            $table->decimal('amount_finance', 10, 2)->nullable();
            
            // Gestion des fichiers
            $table->string('justification_path')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_lines');
    }
};