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
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn('partner_reference_code');
            $table->dropForeign(['local_operational_manager_id']);
            $table->dropColumn('local_operational_manager_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->string('partner_reference_code')->nullable();
            $table->foreignId('local_operational_manager_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
        });
    }
};
