<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove unique from delivery_receipt_partial_receipt
        Schema::table('delivery_receipt_partial_receipt', function (Blueprint $table) {
            $table->dropUnique(['partial_receipt_id', 'delivery_receipt_id']);
        });
        // Remove unique from provisional_acceptance_partial_receipt
        Schema::table('provisional_acceptance_partial_receipt', function (Blueprint $table) {
            $table->dropUnique(['provisional_acceptance_id', 'partial_receipt_id']);
        });
        // Remove unique from final_acceptance_provisional_acceptance
        Schema::table('final_acceptance_provisional_acceptance', function (Blueprint $table) {
            $table->dropUnique(['final_acceptance_id', 'provisional_acceptance_id']);
        });
    }

    public function down(): void
    {
        // Restore unique constraints if needed
        Schema::table('delivery_receipt_partial_receipt', function (Blueprint $table) {
            $table->unique(['partial_receipt_id', 'delivery_receipt_id']);
        });
        Schema::table('provisional_acceptance_partial_receipt', function (Blueprint $table) {
            $table->unique(['provisional_acceptance_id', 'partial_receipt_id']);
        });
        Schema::table('final_acceptance_provisional_acceptance', function (Blueprint $table) {
            $table->unique(['final_acceptance_id', 'provisional_acceptance_id']);
        });
    }
};
