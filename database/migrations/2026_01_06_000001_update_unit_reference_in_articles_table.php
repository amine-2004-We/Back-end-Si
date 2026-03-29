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
        Schema::table('articles', function (Blueprint $table) {
            // Remove old unit column if exists
            if (Schema::hasColumn('articles', 'unit')) {
                $table->dropColumn('unit');
            }
            // Add new unit_id foreign key
            $table->unsignedBigInteger('unit_id')->nullable()->after('reference_price');
            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
            // Optionally, you could add back the old unit column if needed
            // $table->string('unit')->nullable()->after('reference_price');
        });
    }
};
