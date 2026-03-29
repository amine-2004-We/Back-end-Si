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
        Schema::create('transfer_orders', function (Blueprint $table) {
            $table->id();
            
            $table->string('transfer_number')->unique();
            $table->string('beneficiary_name');
            $table->enum('status', ['en_attente', 'envoye', 'valide', 'rejete'])->default('en_attente');
            $table->string('etbac_code')->nullable();
            
            $table->date('issue_date');
            
            $table->decimal('amount', 15, 2);
            
            $table->foreignId('debited_bank_account_id')->constrained('project_bank_accounts');
            $table->foreignId('beneficiary_bank_account_id')->constrained('project_bank_accounts');
            
            $table->string('motif_type')->nullable();
            $table->unsignedBigInteger('motif_id')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['motif_type', 'motif_id']);
            $table->index('status');
            $table->index('issue_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_orders');
    }
};