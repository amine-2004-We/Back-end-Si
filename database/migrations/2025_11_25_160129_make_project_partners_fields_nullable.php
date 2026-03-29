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
        Schema::table('project_partner', function (Blueprint $table) {
            //
            $table->decimal('partner_contribution', 15, 2)->nullable()->change();
            $table->decimal('partner_amount', 15, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_partner', function (Blueprint $table) {
            //
            $table->decimal('partner_contribution', 15, 2)->nullable(false)->change();
            $table->dropColumn('partner_amount');
        });
    }
};
