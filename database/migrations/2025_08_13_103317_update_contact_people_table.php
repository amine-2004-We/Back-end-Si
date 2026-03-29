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
        Schema::table('contact_people', function (Blueprint $table) {
            //
               $table->foreignId('partner_id')->nullable()->change();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contact_people', function (Blueprint $table) {
            //
             $table->dropForeign(['supplier_id']);
            $table->dropColumn('supplier_id');
            $table->foreignId('partner_id')->nullable(false)->change();
        });
    }
};
