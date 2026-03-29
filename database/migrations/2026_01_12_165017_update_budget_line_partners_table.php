<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('budget_line_partner', function (Blueprint $table) {
            $table->dropForeign(['budget_line_id']);
            $table->dropUnique(['budget_line_id', 'partner_id']);
            $table->dropColumn('budget_line_id');

            $table->foreignId('budget_line_project_id')
                ->nullable()
                ->after('id')
                ->constrained('budget_line_project')
                ->restrictOnDelete();

            $table->unique(['budget_line_project_id', 'partner_id']);
        });
    }

    public function down(): void
    {
        Schema::table('budget_line_partner', function (Blueprint $table) {
            $table->dropForeign(['budget_line_project_id']);
            $table->dropUnique(['budget_line_project_id', 'partner_id']);
            $table->dropColumn('budget_line_project_id');

            $table->foreignId('budget_line_id')
                ->after('id')
                ->constrained('budget_lines')
                ->onDelete('restrict');

            $table->unique(['budget_line_id', 'partner_id']);
        });
    }
};
