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
        Schema::table('expense_reports', function (Blueprint $table) {
            $table->dropForeign(['collaborator_id']);

            $table->renameColumn('collaborator_id', 'created_by_id');

            $table->foreign('created_by_id')
                ->references('id')
                ->on('collaborators')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expense_reports', function (Blueprint $table) {
            $table->dropForeign(['created_by_id']);

            $table->renameColumn('created_by_id', 'collaborator_id');

            $table->foreign('collaborator_id')
                ->references('id')
                ->on('collaborators')
                ->cascadeOnDelete();
        });
    }
};
