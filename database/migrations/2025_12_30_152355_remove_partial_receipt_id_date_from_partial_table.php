<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::table('partial_receipts', function (Blueprint $table) {
            // Supprimer l'ancienne clé étrangère si elle existe
            if (Schema::hasColumn('partial_receipts', 'delivery_receipt_id')) {
                $table->dropForeign(['delivery_receipt_id']);
                $table->dropColumn('delivery_receipt_id');
            }
            $table->json('delivery_receipt_ids')->nullable();
        });
    }


    public function down(): void
    {
        Schema::table('partial_receipts', function (Blueprint $table) {
            $table->dropColumn('delivery_receipt_ids');
            $table->foreignId('delivery_receipt_id')->nullable()->constrained('delivery_receipts')->onDelete('cascade');
        });
    }
};
