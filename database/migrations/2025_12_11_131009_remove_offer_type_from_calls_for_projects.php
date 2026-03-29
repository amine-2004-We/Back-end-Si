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
        Schema::table('calls_for_projects', function (Blueprint $table) {
            //
            $table->dropColumn('offer_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calls_for_projects', function (Blueprint $table) {
            //
            $table->string('offer_type')->nullable();
        });
    }
};
