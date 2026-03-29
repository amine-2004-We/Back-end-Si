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
        Schema::table('quotes', function (Blueprint $table) {
            // Vérifier si la colonne n'existe pas avant de l'ajouter
            if (!Schema::hasColumn('quotes', 'purchase_list_id')) {
                $table->foreignId('purchase_list_id')
                    ->nullable()
                    ->after('project_id')
                    ->constrained('purchase_lists')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropForeignIdFor('PurchaseList');
        });
    }
};
