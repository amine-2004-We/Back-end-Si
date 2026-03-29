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
        Schema::table('beneficiary_status_history', function (Blueprint $table) {
            //
            $table->foreignId('destination_group_id')->nullable()->constrained('groups')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beneficiary_status_history', function (Blueprint $table) {
            //
            $table->dropForeign(['destination_group_id']);
            $table->dropColumn('destination_group_id');
        });
    }
};
