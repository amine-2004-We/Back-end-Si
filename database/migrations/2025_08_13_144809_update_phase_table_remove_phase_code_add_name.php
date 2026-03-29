<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('phases', function (Blueprint $table) {
            $table->dropUnique('phases_phase_code_unique'); 
            $table->dropColumn('phase_code');

            $table->string('name')->nullable()->after('id');
        });


        Schema::table('phases', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('phases', function (Blueprint $table) {
            $table->string('phase_code')->after('id');
        });

        Schema::table('phases', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};
