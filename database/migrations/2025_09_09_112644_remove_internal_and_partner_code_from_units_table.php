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
        Schema::table('units', function (Blueprint $table) {
            if (Schema::hasColumn('units', 'internal_code')) {
                $table->dropColumn('internal_code');
            }
            if (Schema::hasColumn('units', 'partner_code')) {
                $table->dropColumn('partner_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->string('internal_code')->nullable();
            $table->string('partner_code')->nullable();
        });
    }
};
