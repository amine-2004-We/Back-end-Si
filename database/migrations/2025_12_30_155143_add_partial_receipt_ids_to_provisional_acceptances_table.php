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
        Schema::table('provisional_acceptances', function (Blueprint $table) {
            $table->json('partial_receipt_ids')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('provisional_acceptances', function (Blueprint $table) {
            $table->dropColumn('partial_receipt_ids');
        });
    }
};
