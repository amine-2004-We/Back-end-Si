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
        Schema::table('partners', function (Blueprint $table) {
            // Ajouter la colonne phase_id avec FK vers la table phases
            if (!Schema::hasColumn('partners', 'phase_id')) {
                $table->foreignId('phase_id')
                    ->nullable()
                    ->constrained('phases')
                    ->onDelete('set null')
                    ->after('status_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            if (Schema::hasColumn('partners', 'phase_id')) {
                $table->dropForeignIdFor('Phase');
                $table->dropColumn('phase_id');
            }
        });
    }
};
