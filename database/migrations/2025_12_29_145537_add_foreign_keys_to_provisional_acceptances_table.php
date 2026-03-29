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
        Schema::table('provisional_acceptances', function (Blueprint $table) {
 
            // Ajout de la clé étrangère vers PartialReceipt
            $table->foreignId('partial_receipt_id')
                  ->nullable()
                  ->constrained('partial_receipts') // Nom de la table source
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('provisional_acceptances', function (Blueprint $table) {
            // On supprime d'abord les clés étrangères, puis les colonnes
            $table->dropForeign(['partial_receipt_id']);
            $table->dropColumn(['partial_receipt_id']);
        });
    }
};
