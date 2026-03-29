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
        Schema::table('recruitment_requests', function (Blueprint $table) {
            $table->foreignId('replaced_collaborator_id')->nullable()->constrained('collaborators');
            $table->string('replacement_reason')->nullable();
            $table->date('exit_date')->nullable();
            $table->foreignId('province_id')->nullable()->constrained('provinces');
            $table->foreignId('project_id')->nullable()->constrained('projects');
            $table->foreignId('position_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recruitment_requests', function (Blueprint $table) {
            $table->dropForeign(['replaced_collaborator_id']);
            $table->dropForeign(['province_id']);
            $table->dropForeign(['position_id']);
            $table->dropForeign(['project_id']);
            $table->dropColumn('replaced_collaborator_id');
            $table->dropColumn('replacement_reason');
            $table->dropColumn('exit_date');
            $table->dropColumn('province_id');
            $table->dropColumn('project_id');
            $table->dropColumn('position_id');
        });
    }
};
