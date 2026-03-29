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
        // Drop the old table
        Schema::dropIfExists('expense_note_payment');
        
        // Create the new table with correct foreign key
        Schema::create('expense_report_payment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->foreignId('expense_report_id')->constrained('expense_reports')->cascadeOnDelete();
            $table->decimal('amount', 15, 2)->nullable();
            $table->timestamps();
            $table->unique(['payment_id', 'expense_report_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_report_payment');
        
        // Recreate the old table
        Schema::create('expense_note_payment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->foreignId('expense_note_id')->constrained('expense_notes')->cascadeOnDelete();
            $table->decimal('amount', 15, 2)->nullable();
            $table->timestamps();
            $table->unique(['payment_id', 'expense_note_id']);
        });
    }
};
