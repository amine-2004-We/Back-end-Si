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
        Schema::table('invoices', function (Blueprint $table) {
            // This column is for the supplier's own invoice number, which you only know upon reception.
            $table->string('supplier_invoice_number')->nullable()->after('invoice_number');

            // The date the invoice was physically received.
            $table->date('reception_date')->nullable()->after('invoice_date');


            // Foreign key for the accounting account.
            // Assuming you have a 'third_party_accounts' table from our previous work.
            $table->foreignId('accounting_account_id')->nullable()->after('notes')
                  ->constrained('third_party_accounts')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['accounting_account_id']);
            $table->dropColumn(['supplier_invoice_number', 'reception_date', 'due_date', 'accounting_account_id']);
        });
    }
};

