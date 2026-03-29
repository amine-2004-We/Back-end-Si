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
        Schema::table('programme_pedagogique', function (Blueprint $table) {
            $table->date('date_prevu')->nullable()->comment('Planned date')->after('end_date');
            $table->date('date_realisation')->nullable()->comment('Actual completion date')->after('date_prevu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programme_pedagogique', function (Blueprint $table) {
            $table->dropColumn(['date_prevu', 'date_realisation']);
        });
    }
};
