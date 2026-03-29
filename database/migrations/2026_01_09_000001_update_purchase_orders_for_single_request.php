<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Supprimer la table pivot si elle existe
        Schema::dropIfExists('purchase_order_purchase_request');

        // Ajouter la colonne purchase_request_id à purchase_orders si elle n'existe pas
        if (!Schema::hasColumn('purchase_orders', 'purchase_request_id')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->foreignId('purchase_request_id')
                    ->nullable()
                    ->after('issuer_id')
                    ->constrained('purchase_requests')
                    ->restrictOnDelete();
            });
        }
    }

    public function down(): void {
        // Supprimer la colonne purchase_request_id
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_orders', 'purchase_request_id')) {
                $table->dropForeign(['purchase_request_id']);
                $table->dropColumn('purchase_request_id');
            }
        });

        // (Optionnel) recréer la table pivot si besoin
        // Schema::create('purchase_order_purchase_request', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
        //     $table->foreignId('purchase_request_id')->constrained('purchase_requests')->restrictOnDelete();
        //     $table->timestamps();
        //     $table->unique(['purchase_order_id', 'purchase_request_id'], 'po_pr_unique');
        // });
    }
};
