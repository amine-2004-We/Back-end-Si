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
        Schema::table('educational_programs', function (Blueprint $table) {
            $table->string('pedagogical_project_arabe')->nullable();
            $table->string('pedagogical_project')->nullable()->change();
            $table->integer('level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('educational_programs', function (Blueprint $table) {
            $table->dropColumn('pedagogical_project_arabe');
            $table->dropColumn('level');
        });
    }
};
