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
            $table->decimal('amount_in_mad', 15, 2)->nullable()->after('amount')
                  ->comment('Montant en MAD, à remplir si la devise n\'est pas MAD');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conventions', function (Blueprint $table) {
            $table->dropColumn('amount_in_mad');
        });
    }
};