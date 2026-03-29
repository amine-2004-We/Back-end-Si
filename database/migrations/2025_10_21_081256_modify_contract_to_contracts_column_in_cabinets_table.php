<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cabinets', function (Blueprint $table) {
            $table->json('contracts')->nullable()->after('address');
        });

        DB::table('cabinets')
            ->whereNotNull('contract')
            ->update([
                'contracts' => DB::raw("jsonb_build_array(contract)")
            ]);

        Schema::table('cabinets', function (Blueprint $table) {
            $table->dropColumn('contract');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cabinets', function (Blueprint $table) {
            $table->string('contract')->nullable()->after('address');
        });

        DB::table('cabinets')
            ->whereNotNull('contracts')
            ->update([
                'contract' => DB::raw("(contracts->>0)")
            ]);

        Schema::table('cabinets', function (Blueprint $table) {
            $table->dropColumn('contracts');
        });
    }
};
