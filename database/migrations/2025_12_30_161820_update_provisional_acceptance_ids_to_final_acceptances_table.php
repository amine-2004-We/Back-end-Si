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
        Schema::table('final_acceptances', function (Blueprint $table) {
            // Supprimer l'ancienne clé étrangère si elle existe
            if (Schema::hasColumn('final_acceptances', 'provisional_acceptance_id')) {
                $table->dropForeign(['provisional_acceptance_id']);
                $table->dropColumn('provisional_acceptance_id');
            }
            // Ajouter la colonne JSON pour stocker plusieurs IDs
            $table->json('provisional_acceptance_ids')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('final_acceptances', function (Blueprint $table) {
            $table->dropColumn('provisional_acceptance_ids');
            $table->foreignId('provisional_acceptance_id')->nullable()->constrained('provisional_acceptances')->onDelete('cascade');
        });
    }
};
