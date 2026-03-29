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
        Schema::table('purchase_request_lines', function (Blueprint $table) {
            //
            $table->dropColumn('unit_price');
            $table->dropColumn('estimated_total');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_request_lines', function (Blueprint $table) {
            //
              $table->decimal('unit_price', 12, 2);
              $table->decimal('estimated_total', 12, 2);
        });
    }
};
