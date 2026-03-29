<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('expense_reports', function (Blueprint $table) {
            $table->id();

            // Liens - mission_order_id nullable selon vos règles
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mission_order_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('budget_line_id')->constrained()->cascadeOnDelete();
            $table->foreignId('collaborator_id')->constrained()->cascadeOnDelete();
            
            // Avance - pointer vers la table advances existante
            $table->foreignId('advance_id')->nullable()->constrained('advances')->nullOnDelete();
            
            // Montant total
            $table->decimal('total_amount', 10, 2)->default(0);
            
            // Statut avec enum
            $table->enum('status', [
                'created', 
                'submitted', 
                'validated_manager', 
                'validated_treasury', 
                'validated_accounting', 
                'rejected'
            ])->default('created');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_reports');
    }
};