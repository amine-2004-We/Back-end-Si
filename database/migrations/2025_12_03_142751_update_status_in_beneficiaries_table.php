<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE beneficiaires DROP CONSTRAINT beneficiaires_status_check');
        Schema::table('beneficiaires', function (Blueprint $table) {
            
            $table->string('status')->default('Inscrit')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beneficiaires', function (Blueprint $table) {
            //
            $table->dropColumn('status');
            $table->enum('status', ['Radié', 'Archivé', 'Actif', 'En pause'])->default('Actif');
        });
    }
};
