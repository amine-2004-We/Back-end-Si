<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('expense_reports', function (Blueprint $table) {
            // Supprimer la contrainte étrangère d'abord
            $table->dropForeign(['budget_line_id']);
            // Puis supprimer la colonne
            $table->dropColumn('budget_line_id');
        });
    }

    public function down()
    {
        Schema::table('expense_reports', function (Blueprint $table) {
            // Recréer la colonne en cas de rollback
            $table->foreignId('budget_line_id')->constrained()->cascadeOnDelete();
        });
    }
};