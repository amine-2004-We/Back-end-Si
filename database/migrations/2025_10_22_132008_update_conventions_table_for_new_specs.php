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
        Schema::table('conventions', function (Blueprint $table) {
            $table->enum('reporting_periodicity', [
                'Trimestriel',
                'Semestriel',
                'Annuel'
            ])->nullable()->after('estimated_end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conventions', function (Blueprint $table) {
            $table->dropColumn('reporting_periodicity');
        });
    }
};
