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
        Schema::table('beneficiaires', function (Blueprint $table) {
            //
            $table->date('insurance_start_date')->nullable();
            $table->date('insurance_end_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beneficiaires', function (Blueprint $table) {
            //
            $table->dropColumn('insurance_start_date');
            $table->dropColumn('insurance_end_date');
        });
    }
};
