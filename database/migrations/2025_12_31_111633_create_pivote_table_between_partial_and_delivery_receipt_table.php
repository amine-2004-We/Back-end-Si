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
        // Créer la table pivot
        Schema::create('delivery_receipt_partial_receipt', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partial_receipt_id')->constrained('partial_receipts')->onDelete('cascade');
            $table->foreignId('delivery_receipt_id')->constrained('delivery_receipts')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['partial_receipt_id', 'delivery_receipt_id']);
        });

        // Supprimer la colonne delivery_receipt_ids si elle existe
        Schema::table('partial_receipts', function (Blueprint $table) {
            if (Schema::hasColumn('partial_receipts', 'delivery_receipt_ids')) {
                $table->dropColumn('delivery_receipt_ids');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_receipt_partial_receipt');
        // Optionnel : remettre la colonne delivery_receipt_ids
        Schema::table('partial_receipts', function (Blueprint $table) {
            if (!Schema::hasColumn('partial_receipts', 'delivery_receipt_ids')) {
                $table->json('delivery_receipt_ids')->nullable();
            }
        });
    }
};