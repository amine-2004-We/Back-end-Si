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
        Schema::table('programme_pedagogique_kader', function (Blueprint $table) {
            $table->foreignId('subcomponent')->nullable()->constrained('phases')->restrictOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programme_pedagogique_kader', function (Blueprint $table) {
            $table->dropForeign(['subcomponent']);

            $table->dropColumn('subcomponent');
        });
    }
};
