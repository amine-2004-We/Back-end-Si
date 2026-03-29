<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('collaborator_training_group as ctg')
            ->leftJoin('collaborators as c', 'ctg.collaborator_id', '=', 'c.id')
            ->whereNull('c.id')
            ->delete();

        Schema::table('collaborator_training_group', function (Blueprint $table) {
            $table->dropForeign('training_group_trainee_collaborator_trainee_collaborator_id_foreign');

            $table->foreign('collaborator_id')
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
        Schema::table('collaborator_training_group', function (Blueprint $table) {
            $table->dropForeign(['collaborator_id']);

            $table->foreign('collaborator_id', 'training_group_trainee_collaborator_trainee_collaborator_id_foreign')
                  ->references('id')
                  ->on('trainee_collaborators')
                  ->cascadeOnDelete();
        });
    }
};

