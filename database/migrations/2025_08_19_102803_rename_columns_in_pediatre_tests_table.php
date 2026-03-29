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
        Schema::table('pediatre_tests', function (Blueprint $table) {
            $table->renameColumn('date_consultation', 'consultation_date');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pediatre_tests', function (Blueprint $table) {
            $table->renameColumn('taille_cm', 'height');
            $table->renameColumn('poids_kg', 'weight');
            $table->renameColumn('a_referer', 'refer_to_center');
            $table->renameColumn('date_consultation', 'consultation_date');
        });
    }
};
